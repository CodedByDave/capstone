<?php

namespace App\DTOs;

/** The column and direction a data table asked to be sorted by. */
final readonly class TableSort
{
    public function __construct(
        public ?string $by = null,
        public string $direction = 'asc',
    ) {}

    public function descending(): bool
    {
        return $this->direction === 'desc';
    }
}
