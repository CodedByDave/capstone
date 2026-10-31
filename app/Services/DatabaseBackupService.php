<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;
use ZipArchive;

/** Creates, lists, prunes and restores database (and upload) backups on the configured disk. */
class DatabaseBackupService
{
    public const TYPES = ['weekly', 'monthly'];

    private const DATABASE_SUFFIX = '.sql.gz';

    private const UPLOADS_SUFFIX = '-uploads.zip';

    public function __construct(private readonly DatabaseDumper $dumper) {}

    /**
     * Writes one backup set and removes sets beyond the retention limit.
     *
     * @return array{database: string, uploads: ?string, pruned: int}
     */
    public function run(string $type): array
    {
        $this->assertType($type);

        $directory = $this->directory($type);
        $base = "laundryhub-{$type}-".now()->format('Ymd-His');
        $database = $this->storeDatabaseDump($directory, $base);
        $uploads = config('backup.include_uploads') ? $this->storeUploads($directory, $base) : null;

        return ['database' => $database, 'uploads' => $uploads, 'pruned' => $this->prune($type)];
    }

    /** @return Collection<int, array{type: string, path: string, size: int, created_at: \Illuminate\Support\Carbon}> Newest first. */
    public function list(): Collection
    {
        $disk = $this->disk();

        return collect(self::TYPES)
            ->flatMap(fn (string $type) => collect($disk->files($this->directory($type)))
                ->filter(fn (string $path) => str_ends_with($path, self::DATABASE_SUFFIX))
                ->map(fn (string $path) => [
                    'type' => $type,
                    'path' => $path,
                    'size' => $disk->size($path),
                    'created_at' => \Illuminate\Support\Carbon::createFromTimestamp($disk->lastModified($path), config('app.timezone')),
                ]))
            ->sortByDesc('created_at')
            ->values();
    }

    /**
     * Replaces the current database with a backup. The caller is responsible for confirming this.
     *
     * @param  string  $path  Database backup path on the backup disk (as returned by list()).
     */
    public function restore(string $path, bool $withUploads = false): void
    {
        $disk = $this->disk();

        if (! $disk->exists($path) || ! str_ends_with($path, self::DATABASE_SUFFIX)) {
            throw new InvalidArgumentException("No database backup found at [{$path}].");
        }

        $local = $this->copyToTemp($path);

        try {
            $this->dumper->load('compress.zlib://'.$local);
        } finally {
            @unlink($local);
        }

        $uploads = $this->uploadsPathFor($path);

        if ($withUploads && $disk->exists($uploads)) {
            $this->restoreUploads($uploads);
        }
    }

    public function uploadsPathFor(string $databasePath): string
    {
        return substr($databasePath, 0, -strlen(self::DATABASE_SUFFIX)).self::UPLOADS_SUFFIX;
    }

    private function storeDatabaseDump(string $directory, string $base): string
    {
        $sql = tempnam(sys_get_temp_dir(), 'dump-');
        $gz = $sql.'.gz';

        try {
            $this->dumper->dump($sql);
            $this->gzip($sql, $gz);
            $this->disk()->putFileAs($directory, new File($gz), $base.self::DATABASE_SUFFIX);
        } finally {
            @unlink($sql);
            @unlink($gz);
        }

        return $directory.'/'.$base.self::DATABASE_SUFFIX;
    }

    private function storeUploads(string $directory, string $base): ?string
    {
        $public = Storage::disk('public');
        $files = $public->allFiles();

        if ($files === []) {
            return null;
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'uploads-');
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create the uploads archive.');
        }

        foreach ($files as $file) {
            $zip->addFile($public->path($file), $file);
        }

        $zip->close();

        try {
            $this->disk()->putFileAs($directory, new File($zipPath), $base.self::UPLOADS_SUFFIX);
        } finally {
            @unlink($zipPath);
        }

        return $directory.'/'.$base.self::UPLOADS_SUFFIX;
    }

    private function restoreUploads(string $uploadsPath): void
    {
        $local = $this->copyToTemp($uploadsPath);
        $zip = new ZipArchive;

        try {
            if ($zip->open($local) !== true) {
                throw new RuntimeException('Could not open the uploads archive.');
            }

            $zip->extractTo(Storage::disk('public')->path(''));
            $zip->close();
        } finally {
            @unlink($local);
        }
    }

    /** Deletes the oldest sets of a type beyond the retention limit and returns how many were removed. */
    private function prune(string $type): int
    {
        $disk = $this->disk();
        $keep = max(1, (int) config("backup.keep.{$type}"));
        $old = collect($disk->files($this->directory($type)))
            ->filter(fn (string $path) => str_ends_with($path, self::DATABASE_SUFFIX))
            ->sortDesc()
            ->slice($keep);

        foreach ($old as $path) {
            $disk->delete([$path, $this->uploadsPathFor($path)]);
        }

        return $old->count();
    }

    private function copyToTemp(string $path): string
    {
        $local = tempnam(sys_get_temp_dir(), 'restore-');
        $source = $this->disk()->readStream($path);
        $target = fopen($local, 'w');
        stream_copy_to_stream($source, $target);
        fclose($target);
        fclose($source);

        return $local;
    }

    private function gzip(string $source, string $target): void
    {
        $in = fopen($source, 'r');
        $out = gzopen($target, 'wb9');

        while (! feof($in)) {
            gzwrite($out, fread($in, 1024 * 1024));
        }

        gzclose($out);
        fclose($in);
    }

    private function directory(string $type): string
    {
        return trim(config('backup.directory'), '/').'/'.$type;
    }

    private function disk(): Filesystem
    {
        return Storage::disk(config('backup.disk'));
    }

    private function assertType(string $type): void
    {
        if (! in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException('Backup type must be one of: '.implode(', ', self::TYPES).'.');
        }
    }
}
