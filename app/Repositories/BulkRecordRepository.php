<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/** Looks up the rows picked in a table for a bulk action, always limited to one shop. */
class BulkRecordRepository
{
    /**
     * @param  class-string<Model>  $model  A soft-deletable model with a `shop_id` column.
     * @param  list<string>  $ids  Values of `$column`.
     * @param  bool  $archived  True to look only at archived rows, false for active ones.
     * @return Collection<int, Model>
     */
    public function forShop(string $model, array $ids, int $shopId, bool $archived, string $column = 'id'): Collection
    {
        $query = $model::query()->where('shop_id', $shopId)->whereIn($column, $ids);

        return ($archived ? $query->onlyTrashed() : $query)->get();
    }
}
