<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ActivityLogService
{
    public function log(
        Model   $subject,
        string  $action,
        ?array  $changes     = null,
        ?int    $shopId      = null,
        ?int    $performedBy = null,
        ?string $module      = null,
    ): ActivityLog {
        return ActivityLog::create([
            'module'       => $module ?? class_basename($subject),  // 'Employee', 'Product', etc.
            'action'       => $action,
            'subject_type' => get_class($subject),
            'subject_id'   => $subject->getKey(),
            'performed_by' => $performedBy ?? auth()->id(),
            'shop_id'      => $shopId,
            'changes'      => $changes,
        ]);
    }

    public function getPaginatedLogs(Shop $shop, object $request): LengthAwarePaginator
    {
        return ActivityLog::query()
            ->where('shop_id', $shop->id)
            ->with(['subject', 'performer:id,name'])
            ->when($request->action ?? null,       fn($q) => $q->where('action', $request->action))
            ->when($request->performed_by ?? null, fn($q) => $q->where('performed_by', (int) $request->performed_by))
            ->when($request->module ?? null,       fn($q) => $q->where('module', $request->module))
            ->when($request->date_from ?? null, fn($q) => $q->where(
                'created_at',
                '>=',
                \Carbon\Carbon::parse($request->date_from, 'Asia/Manila')->startOfDay()->utc()
            ))
            ->when($request->date_to ?? null,   fn($q) => $q->where(
                'created_at',
                '<=',
                \Carbon\Carbon::parse($request->date_to, 'Asia/Manila')->endOfDay()->utc()
            ))
            ->when(
                $request->search ?? null,
                fn($q) =>
                $q->whereHasMorph(
                    'subject',
                    '*',
                    fn($sq, $type) => match (true) {
                        str_ends_with($type, 'Employee') =>
                        $sq->where(
                            fn($s) =>
                            $s->where('first_name', 'like', "%{$request->search}%")
                                ->orWhere('last_name',  'like', "%{$request->search}%")
                        ),
                        default =>
                        $sq->where('name', 'like', "%{$request->search}%"),
                    }
                )
            )
            ->latest()
            ->paginate($request->per_page ?? 10)
            ->withQueryString();
    }

    public function getPerformers(Shop $shop): Collection
    {
        return User::whereIn(
            'id',
            ActivityLog::where('shop_id', $shop->id)
                ->whereNotNull('performed_by')
                ->distinct()
                ->pluck('performed_by')
        )->select('id', 'name')->get();
    }

    public function getModules(Shop $shop): Collection
    {
        return ActivityLog::where('shop_id', $shop->id)
            ->distinct()
            ->pluck('module');
    }
}
