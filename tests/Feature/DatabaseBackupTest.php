<?php

use App\Services\DatabaseBackupService;
use App\Services\DatabaseDumper;
use Illuminate\Support\Facades\Storage;

/** Stands in for mysqldump / mysql so the tests need no MySQL tools. */
class FakeDumper extends DatabaseDumper
{
    public ?string $loaded = null;

    public bool $fail = false;

    public function dump(string $path): void
    {
        if ($this->fail) {
            throw new RuntimeException('mysqldump failed: access denied');
        }

        file_put_contents($path, "-- fake dump\nCREATE TABLE t (id int);\n");
    }

    public function load(string $path): void
    {
        $this->loaded = file_get_contents($path);
    }
}

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
    config(['backup.disk' => 'local', 'backup.directory' => 'backups', 'backup.include_uploads' => true]);
    $this->dumper = new FakeDumper;
    $this->app->instance(DatabaseDumper::class, $this->dumper);
});

it('writes a compressed database dump and a zip of the uploads', function () {
    Storage::disk('public')->put('services/photo.jpg', 'image-bytes');

    $result = app(DatabaseBackupService::class)->run('weekly');

    expect($result['database'])->toStartWith('backups/weekly/laundryhub-weekly-')->toEndWith('.sql.gz');
    Storage::disk('local')->assertExists($result['database']);
    expect(gzdecode(Storage::disk('local')->get($result['database'])))->toContain('CREATE TABLE t');

    Storage::disk('local')->assertExists($result['uploads']);
    $zip = new ZipArchive;
    $zip->open(Storage::disk('local')->path($result['uploads']));
    expect($zip->getFromName('services/photo.jpg'))->toBe('image-bytes');
    $zip->close();
});

it('skips the uploads archive when there are no uploaded files', function () {
    $result = app(DatabaseBackupService::class)->run('monthly');

    expect($result['uploads'])->toBeNull();
});

it('keeps only the newest backups of a type', function () {
    config(['backup.keep.weekly' => 2]);
    $disk = Storage::disk('local');
    foreach (['20260101-000000', '20260108-000000', '20260115-000000'] as $stamp) {
        $disk->put("backups/weekly/laundryhub-weekly-{$stamp}.sql.gz", 'old');
        $disk->put("backups/weekly/laundryhub-weekly-{$stamp}-uploads.zip", 'old');
    }

    $result = app(DatabaseBackupService::class)->run('weekly');

    expect($result['pruned'])->toBe(2);
    $disk->assertMissing('backups/weekly/laundryhub-weekly-20260101-000000.sql.gz');
    $disk->assertMissing('backups/weekly/laundryhub-weekly-20260101-000000-uploads.zip');
    $disk->assertMissing('backups/weekly/laundryhub-weekly-20260108-000000.sql.gz');
    $disk->assertExists('backups/weekly/laundryhub-weekly-20260115-000000.sql.gz');
    $disk->assertExists($result['database']);
});

it('does not prune monthly backups when a weekly one runs', function () {
    Storage::disk('local')->put('backups/monthly/laundryhub-monthly-20260101-000000.sql.gz', 'monthly');

    app(DatabaseBackupService::class)->run('weekly');

    Storage::disk('local')->assertExists('backups/monthly/laundryhub-monthly-20260101-000000.sql.gz');
});

it('rejects an unknown backup type', function () {
    expect(fn () => app(DatabaseBackupService::class)->run('daily'))->toThrow(InvalidArgumentException::class);
});

it('lists backups of both types, newest first', function () {
    $disk = Storage::disk('local');
    $disk->put('backups/weekly/laundryhub-weekly-a.sql.gz', 'x');
    touch($disk->path('backups/weekly/laundryhub-weekly-a.sql.gz'), time() - 3600);
    $disk->put('backups/monthly/laundryhub-monthly-b.sql.gz', 'x');

    $list = app(DatabaseBackupService::class)->list();

    expect($list->pluck('type')->all())->toBe(['monthly', 'weekly']);
});

it('restores a backup by loading the decompressed SQL', function () {
    $service = app(DatabaseBackupService::class);
    $path = $service->run('weekly')['database'];

    $service->restore($path);

    expect($this->dumper->loaded)->toContain('CREATE TABLE t');
});

it('restores uploaded files when asked', function () {
    Storage::disk('public')->put('services/photo.jpg', 'image-bytes');
    $service = app(DatabaseBackupService::class);
    $path = $service->run('weekly')['database'];
    Storage::disk('public')->delete('services/photo.jpg');

    $service->restore($path, withUploads: true);

    expect(Storage::disk('public')->get('services/photo.jpg'))->toBe('image-bytes');
});

it('refuses to restore a path that is not a backup', function () {
    expect(fn () => app(DatabaseBackupService::class)->restore('backups/weekly/missing.sql.gz'))
        ->toThrow(InvalidArgumentException::class);
});

it('reports success and failure through the backup command', function () {
    $this->artisan('backup:run', ['--type' => 'weekly'])->assertSuccessful();

    $this->dumper->fail = true;
    $this->artisan('backup:run', ['--type' => 'weekly'])->assertFailed();
});

it('asks before restoring and changes nothing when declined', function () {
    $path = app(DatabaseBackupService::class)->run('weekly')['database'];

    $this->artisan('backup:restore', ['path' => $path])->expectsConfirmation('Restore '.$path.' now?', 'no')->assertSuccessful();

    expect($this->dumper->loaded)->toBeNull();
});

it('schedules a weekly and a monthly backup', function () {
    $events = collect(app(Illuminate\Console\Scheduling\Schedule::class)->events())
        ->map(fn ($event) => $event->command)
        ->filter(fn ($command) => str_contains($command, 'backup:run'));

    expect($events->implode(' '))->toContain('--type=weekly')->toContain('--type=monthly');
});
