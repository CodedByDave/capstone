<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Mime\MimeTypes;

/**
 * Keeps KYC business documents (BIR, DTI, Mayor's and Sanitary permits) encrypted at rest.
 *
 * Each file is encrypted with the application key (AES-256-CBC with a MAC, through Laravel's
 * encrypter) before it is written to the private disk, and saved as "<random>.<ext>.enc".
 * Only the Super Admin's document viewer decrypts it, in memory, when the document is opened.
 * Files saved before encryption was added have no ".enc" suffix and are read as they are, until
 * `php artisan kyc:encrypt-existing` encrypts them.
 */
class KycDocumentVault
{
    public const DISK = 'private';

    public const SUFFIX = '.enc';

    /** The only folders KYC documents are kept in. */
    public const DIRECTORIES = ['kyc', 'kyc-verifications'];

    /** Encrypts an uploaded document into the folder and returns its stored path. */
    public function store(UploadedFile $upload, string $directory): string
    {
        $contents = file_get_contents($upload->getRealPath());

        if ($contents === false) {
            throw new RuntimeException('The uploaded document could not be read.');
        }

        return $this->storeContents($contents, $directory, $upload->guessExtension() ?: $upload->getClientOriginalExtension() ?: 'bin');
    }

    /** Encrypts raw document bytes into the folder and returns the stored path. */
    public function storeContents(string $contents, string $directory, string $extension): string
    {
        $path = trim($directory, '/').'/'.Str::random(40).'.'.strtolower($extension).self::SUFFIX;

        if (! Storage::disk(self::DISK)->put($path, Crypt::encryptString($contents))) {
            throw new RuntimeException('The document could not be saved.');
        }

        return $path;
    }

    /**
     * Copies a stored document (kept from a rejected application) into a new folder. The copy
     * stays encrypted with the same key; a document saved before encryption is encrypted now.
     */
    public function copy(string $from, string $directory): string
    {
        if ($this->isEncrypted($from)) {
            $to = trim($directory, '/').'/'.basename($from);
            Storage::disk(self::DISK)->copy($from, $to);

            return $to;
        }

        return $this->storeContents($this->contents($from), $directory, $this->extension($from));
    }

    public function exists(?string $path): bool
    {
        return $path !== null && $path !== '' && Storage::disk(self::DISK)->exists($path);
    }

    /**
     * The document's original bytes, decrypted.
     *
     * @throws RuntimeException when the file is missing or cannot be decrypted (wrong key or tampered)
     */
    public function contents(string $path): string
    {
        $stored = Storage::disk(self::DISK)->get($path);

        if ($stored === null) {
            throw new RuntimeException('The document file is missing.');
        }

        if (! $this->isEncrypted($path)) {
            return $stored;
        }

        try {
            return Crypt::decryptString($stored);
        } catch (DecryptException $exception) {
            throw new RuntimeException('The document could not be decrypted.', previous: $exception);
        }
    }

    /** The content type of the original document, from its extension. */
    public function mimeType(string $path): string
    {
        return MimeTypes::getDefault()->getMimeTypes($this->extension($path))[0] ?? 'application/octet-stream';
    }

    /** A file name to show the admin, without the encryption suffix. */
    public function displayName(string $path): string
    {
        return Str::beforeLast(basename($path), self::SUFFIX);
    }

    public function isEncrypted(string $path): bool
    {
        return str_ends_with($path, self::SUFFIX);
    }

    /** Whether the path points inside one of the KYC folders (no traversal, no other private files). */
    public function isKycPath(string $path): bool
    {
        if (str_contains($path, '..') || str_contains($path, '\\') || str_starts_with($path, '/')) {
            return false;
        }

        return in_array(Str::before($path, '/'), self::DIRECTORIES, true) && substr_count($path, '/') === 2;
    }

    /**
     * Encrypts a document saved before encryption was added and returns its new path, or null when
     * it is already encrypted. The plaintext file is deleted only after the encrypted copy is saved
     * and reads back to the same bytes.
     */
    public function encryptLegacy(string $path): ?string
    {
        if ($this->isEncrypted($path)) {
            return null;
        }

        $plain = $this->contents($path);
        $directory = Str::beforeLast($path, '/');
        $encryptedPath = $directory.'/'.Str::beforeLast(basename($path), '.').'.'.$this->extension($path).self::SUFFIX;

        Storage::disk(self::DISK)->put($encryptedPath, Crypt::encryptString($plain));

        if ($this->contents($encryptedPath) !== $plain) {
            Storage::disk(self::DISK)->delete($encryptedPath);
            throw new RuntimeException("Encrypted copy of {$path} did not verify.");
        }

        return $encryptedPath;
    }

    public function delete(string $path): void
    {
        Storage::disk(self::DISK)->delete($path);
    }

    private function extension(string $path): string
    {
        return strtolower(pathinfo($this->displayName($path), PATHINFO_EXTENSION)) ?: 'bin';
    }
}
