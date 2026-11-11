<?php

namespace App\Console\Commands;

use App\Services\KycDocumentEncryptionService;
use Illuminate\Console\Command;

class EncryptExistingKycDocuments extends Command
{
    protected $signature = 'kyc:encrypt-existing {--dry-run : List the documents that would be encrypted without changing anything}';

    protected $description = 'Encrypt KYC documents that were saved before encryption at rest was added (safe to run more than once)';

    public function handle(KycDocumentEncryptionService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $result = $service->encryptExisting($dryRun, function (string $path, ?string $newPath, ?string $error): void {
            if ($error !== null) {
                $this->error("  failed  {$path}: {$error}");
            } elseif ($this->output->isVerbose() || $newPath === null) {
                $this->line('  '.($newPath === null ? 'would encrypt' : 'encrypted')."  {$path}");
            }
        });

        $this->info(sprintf(
            '%s %d document(s), %d already encrypted, %d failed%s.',
            $dryRun ? 'Would encrypt' : 'Encrypted',
            $result['encrypted'],
            $result['already'],
            $result['failed'],
            $dryRun ? '' : ", {$result['references']} database reference(s) updated",
        ));

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
