<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class ListBackups extends Command
{
    protected $signature = 'backup:list';

    protected $description = 'List the available database backups, newest first';

    public function handle(DatabaseBackupService $backups): int
    {
        $rows = $backups->list()->map(fn (array $backup) => [
            $backup['type'],
            $backup['path'],
            number_format($backup['size'] / 1024, 1).' KB',
            $backup['created_at']->toDateTimeString(),
        ]);

        if ($rows->isEmpty()) {
            $this->warn('No backups found. Run `php artisan backup:run` first.');

            return self::SUCCESS;
        }

        $this->table(['Type', 'Path', 'Size', 'Created'], $rows->all());

        return self::SUCCESS;
    }
}
