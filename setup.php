<?php
if ($_GET['key'] !== 'laundry2026') die('Unauthorized');
chdir(__DIR__);
$commands = [
    'php artisan key:generate --force 2>&1',
    'php artisan migrate --force 2>&1',
    'php artisan config:cache 2>&1',
    'php artisan route:cache 2>&1',
    'php artisan view:cache 2>&1',
    'php artisan storage:link 2>&1',
    'chmod -R 775 storage bootstrap/cache 2>&1',
];
foreach ($commands as $cmd) {
    echo "<pre>\$ $cmd\n" . shell_exec($cmd) . "</pre>";
}
echo "<b>Done!</b>";
