<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class RunBackup extends Command
{
    protected $signature = 'backup:run {--type=weekly : weekly or monthly (sets which retention limit applies)}';

    protected $description = 'Back up the database (and uploaded files) to the configured backup disk';

    public function handle(DatabaseBackupService $backups): int
    {
        try {
            $result = $backups->run((string) $this->option('type'));
        } catch (Throwable $e) {
            Log::error('Backup failed', ['type' => $this->option('type'), 'error' => $e->getMessage()]);
            $this->error('Backup failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Database: '.$result['database']);
        $this->info('Uploads:  '.($result['uploads'] ?? 'none to back up'));
        $result['pruned'] > 0 && $this->line("Removed {$result['pruned']} old backup(s).");

        return self::SUCCESS;
    }
}
