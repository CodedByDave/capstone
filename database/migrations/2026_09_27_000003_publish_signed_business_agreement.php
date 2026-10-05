<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $version = (string) config('business_agreement.version');
        $content = (string) config('business_agreement.content');
        $now = now();

        DB::table('business_agreements')->update(['is_active' => false]);

        DB::table('business_agreements')->updateOrInsert(
            ['version' => $version],
            [
                'public_id' => (string) Str::ulid(),
                'title' => config('business_agreement.title'),
                'content' => $content,
                'content_hash' => hash('sha256', $content),
                'effective_at' => config('business_agreement.effective_at'),
                'published_at' => $now,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );
    }

    public function down(): void
    {
        DB::table('business_agreements')
            ->where('version', config('business_agreement.version'))
            ->update(['is_active' => false]);

        DB::table('business_agreements')
            ->where('version', '1.0')
            ->update(['is_active' => true]);
    }
};
