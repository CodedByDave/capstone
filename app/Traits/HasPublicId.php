<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Gives a model a ULID `public_id` that is used in URLs instead of the sequential id, so a URL
 * never reveals how many records exist or lets someone guess the next one.
 */
trait HasPublicId
{
    public static function bootHasPublicId(): void
    {
        static::creating(function ($model): void {
            $model->public_id ??= (string) Str::ulid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
