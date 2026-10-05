<?php

use App\Repositories\AdminDashboardRepository;
use App\Services\Admin\AnalyticsService;
use Illuminate\Support\Carbon;
use Mockery\MockInterface;

afterEach(fn () => Mockery::close());

test('analytics service builds trends charts and top shop presentation data', function () {
    $repository = Mockery::mock(AdminDashboardRepository::class, function (MockInterface $mock) {
        $mock->shouldReceive('shopsCountSince')->once()->andReturn(2);
        $mock->shouldReceive('shopsCountBetween')->once()->andReturn(0);
        $mock->shouldReceive('usersCountByRoleSince')->once()->andReturn(2);
        $mock->shouldReceive('usersCountByRoleBetween')->once()->andReturn(4);
        $mock->shouldReceive('revenueBetween')->twice()->andReturn(200.0, 0.0);
        $mock->shouldReceive('ordersCountByStatusSince')->once()->andReturn(3);
        $mock->shouldReceive('ordersCountByStatusBetween')->once()->andReturn(2);
        $mock->shouldReceive('shopsTotalCount')->once()->andReturn(10);
        $mock->shouldReceive('usersCountByRole')->once()->andReturn(20);
        $mock->shouldReceive('totalRevenue')->once()->andReturn(1500.0);
        $mock->shouldReceive('ordersCountByStatus')->once()->andReturn(8);
        $mock->shouldReceive('revenueByMonthBetween')->once()->andReturn(collect([
            '2026-09' => 200,
        ]));
        $mock->shouldReceive('ordersByMonthBetween')->once()->andReturn(collect([
            '2026-09' => 3,
        ]));
        $mock->shouldReceive('usersByMonthForRoleBetween')->once()->andReturn(collect([
            '2026-09' => 2,
        ]));
        $mock->shouldReceive('getTopPerformingShops')->once()->andReturn(collect([
            (object) [
                'shop_name' => 'Clean Laundry',
                'barangay' => 'Central',
                'municipality' => 'Manila',
                'revenue' => '300',
                'orders' => '4',
                'revenue_this_month' => '150',
                'revenue_last_month' => '100',
            ],
        ]));
    });

    $overview = (new AnalyticsService($repository))->getOverview(
        Carbon::parse('2026-09-15 12:00:00'),
    );

    expect($overview['stats'])
        ->toMatchArray([
            'total_shops' => ['value' => 10, 'change' => 100],
            'total_customers' => ['value' => 20, 'change' => -50.0],
            'total_revenue' => ['value' => 1500.0, 'change' => 100],
            'total_orders' => ['value' => 8, 'change' => 50.0],
        ])
        ->and($overview['revenue_chart'])->toHaveCount(12)
        ->and($overview['revenue_chart']->last())->toBe([
            'month' => 'Sep',
            'amount' => 200.0,
        ])
        ->and($overview['orders_chart']->last())->toBe([
            'month' => 'Sep',
            'count' => 3,
        ])
        ->and($overview['registrations_chart']->last())->toBe([
            'month' => 'Sep',
            'count' => 2,
        ])
        ->and($overview['top_shops']->all())->toBe([[
            'rank' => 1,
            'name' => 'Clean Laundry',
            'location' => 'Central, Manila',
            'revenue' => 300.0,
            'orders' => 4,
            'growth' => 50.0,
        ]]);
});
