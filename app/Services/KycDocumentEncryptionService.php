<?php

namespace App\Services;

use App\Repositories\KycVerificationRepository;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * One-time upgrade: encrypts the KYC documents that were saved before encryption at rest was added,
 * and points the orders and verifications that use them at the encrypted copies.
 */
class KycDocumentEncryptionService
{
    public function __construct(
        private readonly KycDocumentVault $vault,
        private readonly OrderRepository $orders,
        private readonly KycVerificationRepository $verifications,
    ) {}

    /**
     * @param  callable(string, string|null, string|null): void|null  $progress  called per file with (path, new path, error)
     * @return array{encrypted: int, already: int, failed: int, references: int}
     */
    public function encryptExisting(bool $dryRun = false, ?callable $progress = null): array
    {
        $result = ['encrypted' => 0, 'already' => 0, 'failed' => 0, 'references' => 0];
        $disk = Storage::disk(KycDocumentVault::DISK);

        foreach (KycDocumentVault::DIRECTORIES as $directory) {
            foreach ($disk->allFiles($directory) as $path) {
                if ($this->vault->isEncrypted($path)) {
                    $result['already']++;

                    continue;
                }

                if ($dryRun) {
                    $result['encrypted']++;
                    $progress && $progress($path, null, null);

                    continue;
                }

                try {
                    $encryptedPath = $this->vault->encryptLegacy($path);

                    // The rows switch to the encrypted copy together, or not at all.
                    $result['references'] += DB::transaction(fn () => $this->orders->replaceKycDocumentPath($path, $encryptedPath)
                        + $this->verifications->replaceDocumentPath($path, $encryptedPath));

                    // Only now, with every row pointing at the verified encrypted copy, is the plaintext removed.
                    $this->vault->delete($path);
                    $result['encrypted']++;
                    $progress && $progress($path, $encryptedPath, null);
                } catch (Throwable $exception) {
                    report($exception);
                    $result['failed']++;
                    $progress && $progress($path, null, $exception->getMessage());
                }
            }
        }

        return $result;
    }
}
