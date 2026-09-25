<?php

namespace Database\Seeders;

use App\Models\IssueReport;
use App\Models\User;
use Illuminate\Database\Seeder;

class IssueReportSeeder extends Seeder
{
    public function run(): void
    {
        $reporters = User::query()
            ->where('role', '!=', 'super_admin')
            ->orderBy('id')
            ->limit(10)
            ->get();

        $reports = [
            [
                'subject' => 'Mobile checkout button freezes',
                'description' => 'The checkout button stops responding after selecting GCash on a mobile device. Refreshing the page clears the selected plan.',
                'category' => 'bug',
                'priority' => 'critical',
                'status' => 'open',
                'page_url' => '/checkout/confirm',
                'browser' => 'Chrome 129 · Android 14',
                'admin_notes' => 'Reproduction is pending on an Android device.',
                'created_at' => now()->subHours(3),
            ],
            [
                'subject' => 'GCash payment still shows as pending',
                'description' => 'Payment was completed and deducted from the wallet, but the subscription order continues to display a pending status.',
                'category' => 'billing',
                'priority' => 'high',
                'status' => 'in_progress',
                'page_url' => '/shop/dashboard',
                'browser' => 'Safari · iPhone 15',
                'admin_notes' => 'Checking the payment webhook and transaction reference.',
                'created_at' => now()->subDay(),
            ],
            [
                'subject' => 'Unable to reset account password',
                'description' => 'The password reset email arrives, but the link returns to the login page without allowing a new password.',
                'category' => 'account',
                'priority' => 'high',
                'status' => 'open',
                'page_url' => '/forgot-password',
                'browser' => 'Microsoft Edge 129 · Windows 11',
                'admin_notes' => null,
                'created_at' => now()->subDays(2),
            ],
            [
                'subject' => 'Dashboard statistics do not refresh',
                'description' => 'The revenue and order totals remain unchanged after a successful payment until the browser is refreshed.',
                'category' => 'bug',
                'priority' => 'medium',
                'status' => 'resolved',
                'page_url' => '/admin/dashboard',
                'browser' => 'Chrome 129 · Windows 11',
                'admin_notes' => 'Resolved by refreshing dashboard props after a payment event.',
                'resolved_at' => now()->subDays(2),
                'created_at' => now()->subDays(5),
            ],
            [
                'subject' => 'CSV export is missing filtered rows',
                'description' => 'Exporting the filtered order table produces a file that does not contain all records visible in the selected date range.',
                'category' => 'bug',
                'priority' => 'high',
                'status' => 'in_progress',
                'page_url' => '/admin/orders',
                'browser' => 'Firefox 131 · Windows 11',
                'admin_notes' => 'Comparing table and export query parameters.',
                'created_at' => now()->subDays(7),
            ],
            [
                'subject' => 'Some text is difficult to read in dark mode',
                'description' => 'Muted labels on the shop details page have very low contrast when dark mode is enabled.',
                'category' => 'bug',
                'priority' => 'low',
                'status' => 'resolved',
                'page_url' => '/admin/shop',
                'browser' => 'Chrome 128 · macOS',
                'admin_notes' => 'Updated muted foreground colors to meet contrast requirements.',
                'resolved_at' => now()->subDays(6),
                'created_at' => now()->subDays(10),
            ],
            [
                'subject' => 'Add email notifications for issue updates',
                'description' => 'Please notify the reporter by email whenever an administrator changes the issue status or adds a resolution.',
                'category' => 'feature_request',
                'priority' => 'low',
                'status' => 'open',
                'page_url' => '/user/dashboard',
                'browser' => 'Chrome 129 · Android 13',
                'admin_notes' => 'Candidate for the next support workflow milestone.',
                'created_at' => now()->subDays(12),
            ],
            [
                'subject' => 'Subscription was charged twice',
                'description' => 'Two charges with the same amount appeared after the payment page was submitted more than once.',
                'category' => 'billing',
                'priority' => 'critical',
                'status' => 'resolved',
                'page_url' => '/checkout/confirm',
                'browser' => 'Safari · iPhone 14',
                'admin_notes' => 'Duplicate charge refunded and payment submission made idempotent.',
                'resolved_at' => now()->subDays(8),
                'created_at' => now()->subDays(14),
            ],
            [
                'subject' => 'Staff permissions are not saved',
                'description' => 'Permissions selected for the cashier role disappear after leaving and returning to the Roles and Permissions page.',
                'category' => 'account',
                'priority' => 'high',
                'status' => 'open',
                'page_url' => '/shop/permission',
                'browser' => 'Microsoft Edge 128 · Windows 10',
                'admin_notes' => null,
                'created_at' => now()->subDays(18),
            ],
            [
                'subject' => 'Pages occasionally load slowly',
                'description' => 'The order and inventory pages sometimes take several seconds to load during busy hours.',
                'category' => 'general',
                'priority' => 'medium',
                'status' => 'closed',
                'page_url' => '/shop/dashboard',
                'browser' => 'Chrome 128 · Windows 10',
                'admin_notes' => 'Monitoring completed; no continuing performance degradation found.',
                'created_at' => now()->subDays(25),
            ],
        ];

        foreach ($reports as $index => $attributes) {
            $createdAt = $attributes['created_at'];
            unset($attributes['created_at']);

            $report = IssueReport::updateOrCreate(
                ['subject' => $attributes['subject']],
                array_merge($attributes, [
                    'user_id' => $reporters->isEmpty()
                        ? null
                        : $reporters[$index % $reporters->count()]->id,
                ]),
            );

            $report->timestamps = false;
            $report->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $attributes['resolved_at'] ?? $createdAt,
            ])->save();
        }
    }
}
