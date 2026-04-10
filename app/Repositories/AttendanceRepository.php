<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Models\Shop;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttendanceRepository extends Repository
{
    public function __construct(Attendance $attendance)
    {
        parent::__construct($attendance);
    }

    public function getPaginated(Shop $shop, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Attendance::with(['employee', 'marker:id,name'])
            ->where('shop_id', $shop->id);

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        return $query->latest('date')->paginate($perPage);
    }

    public function getByDate(Shop $shop, string $date, ?string $branch = null, ?int $excludeUserId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Attendance::with(['employee', 'marker:id,name'])
            ->where('shop_id', $shop->id)
            ->whereDate('date', $date);

        if ($branch !== null || $excludeUserId !== null) {
            $query->whereHas('employee', function ($q) use ($branch, $excludeUserId) {
                if ($branch !== null) {
                    $q->where('branch_name', $branch);
                }
                if ($excludeUserId !== null) {
                    $q->where(function ($inner) use ($excludeUserId) {
                        $inner->where('user_id', '!=', $excludeUserId)
                              ->orWhereNull('user_id');
                    });
                }
            });
        }

        return $query->get();
    }

    public function getStats(Shop $shop, string $date, ?string $branch = null, ?int $excludeUserId = null): array
    {
        $base = Attendance::where('shop_id', $shop->id)->whereDate('date', $date);

        if ($branch !== null || $excludeUserId !== null) {
            $base->whereHas('employee', function ($q) use ($branch, $excludeUserId) {
                if ($branch !== null) {
                    $q->where('branch_name', $branch);
                }
                if ($excludeUserId !== null) {
                    $q->where(function ($inner) use ($excludeUserId) {
                        $inner->where('user_id', '!=', $excludeUserId)
                              ->orWhereNull('user_id');
                    });
                }
            });
        }

        return [
            'present'  => (clone $base)->where('status', 'present')->count(),
            'absent'   => (clone $base)->where('status', 'absent')->count(),
            'late'     => (clone $base)->where('status', 'late')->count(),
            'half_day' => (clone $base)->where('status', 'half_day')->count(),
        ];
    }
}
