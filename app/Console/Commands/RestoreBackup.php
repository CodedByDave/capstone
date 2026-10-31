<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Throwable;

class RestoreBackup extends Command
{
    protected $signature = 'backup:restore {path : Backup path from `backup:list`} {--uploads : Also restore the uploaded files} {--force : Skip the confirmation}';

    protected $description = 'Replace the current database with a backup';

    public function handle(DatabaseBackupService $backups): int
    {
        $this->warn('This replaces ALL current data in the database ['.config('database.connections.'.config('database.default').'.database').'] with the backup.');

        if (! $this->option('force') && ! $this->confirm('Restore '.$this->argument('path').' now?')) {
            $this->line('Cancelled. Nothing was changed.');

            return self::SUCCESS;
        }

        try {
            $backups->restore((string) $this->argument('path'), (bool) $this->option('uploads'));
        } catch (Throwable $e) {
            $this->error('Restore failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Restore complete.');

        return self::SUCCESS;
    }
}
