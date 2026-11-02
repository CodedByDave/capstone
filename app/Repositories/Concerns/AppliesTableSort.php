<?php

namespace App\Repositories\Concerns;

use App\DTOs\TableSort;
use Closure;
use Illuminate\Database\Eloquent\Builder;

trait AppliesTableSort
{
    /**
     * Orders the query by a whitelisted table column. Each map entry is a column name or a
     * closure(query, direction) for relation and computed columns. An unknown or missing column
     * keeps the default order, and the tie-breaker keeps rows stable across pages.
     *
     * @param  array<string, string|Closure>  $columns
     */
    protected function applyTableSort(
        Builder $query,
        ?TableSort $sort,
        array $columns,
        string $tieBreaker = 'id',
    ): bool {
        if ($sort === null || $sort->by === null || ! isset($columns[$sort->by])) {
            return false;
        }

        $direction = $sort->descending() ? 'desc' : 'asc';
        $target = $columns[$sort->by];

        if ($target instanceof Closure) {
            $target($query, $direction);
        } else {
            $query->orderBy($target, $direction);
        }

        $query->orderBy($tieBreaker, 'asc');

        return true;
    }
}
