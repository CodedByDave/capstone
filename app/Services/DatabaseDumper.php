<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

/** Wraps the mysqldump / mysql command line tools so the backup service can be tested without them. */
class DatabaseDumper
{
    /** Writes a complete SQL dump of the default connection's database to `$path`. */
    public function dump(string $path): void
    {
        $connection = $this->connection();

        $process = new Process([
            config('backup.mysqldump'),
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            '--single-transaction',
            '--routines',
            '--triggers',
            '--no-tablespaces',
            '--default-character-set=utf8mb4',
            '--result-file='.$path,
            $connection['database'],
        ], env: ['MYSQL_PWD' => (string) $connection['password']], timeout: config('backup.timeout'));

        $this->run($process, 'mysqldump');
    }

    /** Loads a plain SQL file (or a gzip stream URL such as compress.zlib://file.sql.gz) into the database. */
    public function load(string $path): void
    {
        $connection = $this->connection();

        $process = new Process([
            config('backup.mysql'),
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            '--default-character-set=utf8mb4',
            $connection['database'],
        ], env: ['MYSQL_PWD' => (string) $connection['password']], timeout: config('backup.timeout'));

        $process->setInput(fopen($path, 'r'));

        $this->run($process, 'mysql');
    }

    /** @return array{host: string, port: string|int, username: string, password: ?string, database: string} */
    private function connection(): array
    {
        $connection = config('database.connections.'.config('database.default'));

        if (($connection['driver'] ?? null) !== 'mysql' && ($connection['driver'] ?? null) !== 'mariadb') {
            throw new RuntimeException('Backups are only supported for MySQL / MariaDB connections.');
        }

        return $connection;
    }

    private function run(Process $process, string $tool): void
    {
        $process->run();

        if (! $process->isSuccessful()) {
            // The password travels in the environment, never the arguments, so the error output is safe to show.
            throw new RuntimeException("{$tool} failed: ".trim($process->getErrorOutput() ?: $process->getOutput()));
        }
    }
}
