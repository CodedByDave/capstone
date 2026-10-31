<?php

/**
 * Scheduled database and upload backups (see App\Services\DatabaseBackupService).
 *
 * A backup kept on the application's own server only protects against corrupted data, not against
 * losing the server. Point BACKUP_DISK at an off-server disk (for example an S3 disk defined in
 * config/filesystems.php) or copy the backups folder elsewhere.
 */

// Local XAMPP installs keep the MySQL tools beside PHP, so look there when no path is configured.
$xamppBin = dirname(PHP_BINARY, 2).DIRECTORY_SEPARATOR.'mysql'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR;
$exe = DIRECTORY_SEPARATOR === '\\' ? '.exe' : '';

return [
    // Filesystem disk the backups are written to.
    'disk' => env('BACKUP_DISK', 'local'),

    // Folder on that disk.
    'directory' => env('BACKUP_DIRECTORY', 'backups'),

    'mysqldump' => env('BACKUP_MYSQLDUMP_PATH', is_file($xamppBin.'mysqldump'.$exe) ? $xamppBin.'mysqldump'.$exe : 'mysqldump'),

    'mysql' => env('BACKUP_MYSQL_PATH', is_file($xamppBin.'mysql'.$exe) ? $xamppBin.'mysql'.$exe : 'mysql'),

    // Also zip the public uploads (service photos, delivery proofs, cover photos).
    'include_uploads' => env('BACKUP_INCLUDE_UPLOADS', true),

    // How many backups of each kind to keep before the oldest are deleted.
    'keep' => [
        'weekly' => (int) env('BACKUP_KEEP_WEEKLY', 4),
        'monthly' => (int) env('BACKUP_KEEP_MONTHLY', 3),
    ],

    'timeout' => (int) env('BACKUP_TIMEOUT', 600),
];
