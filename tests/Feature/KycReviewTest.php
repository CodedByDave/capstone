<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\KycReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KycReviewTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $overrides = []): Order
    {
        return new Order($overrides + [
            'shop_name' => 'Suds & Bubbles Laundry',
            'block_street' => '12 Rizal St',
            'barangay' => 'Poblacion',
            'municipality' => 'Imus',
            'postal_code' => '4103',
            'bir_expiry_date' => '2027-12-31',
            'kyc_ocr_data' => ['bir' => [
                'tin' => '123-456-789-000',
                'tax_type' => 'vat',
                'registered_name' => 'SUDS AND BUBBLES LAUNDRY',
                'registered_address' => '12 Rizal St, Poblacion, Imus, Cavite',
                'expiry_date' => '2027-06-30',
            ]],
            'kyc_confirmed_data' => ['tin' => '123-456-789-000', 'tax_type' => 'non_vat', 'registered_name' => 'Suds & Bubbles Laundry', 'registered_address' => '12 Rizal St'],
        ]);
    }

    public function test_flags_an_expiry_date_that_differs_from_the_document(): void
    {
        $rows = collect((new KycReviewService)->rows($this->order()))->keyBy('key');

        $this->assertSame('mismatch', $rows['bir_expiry_date']['status']);
        $this->assertSame('2027-06-30', $rows['bir_expiry_date']['ocr']);
        $this->assertNull($rows['bir_expiry_date']['typed']);
        $this->assertSame('2027-12-31', $rows['bir_expiry_date']['confirmed']);
    }

    public function test_matching_name_address_and_tin_are_marked_as_match(): void
    {
        $rows = collect((new KycReviewService)->rows($this->order()))->keyBy('key');

        $this->assertSame('match', $rows['tin']['status']);
        $this->assertSame('match', $rows['registered_address']['status']);
    }

    public function test_a_tax_type_the_owner_changed_from_the_ocr_reading_is_a_mismatch(): void
    {
        $rows = collect((new KycReviewService)->rows($this->order()))->keyBy('key');

        $this->assertSame('VAT', $rows['tax_type']['ocr']);
        $this->assertSame('Non-VAT', $rows['tax_type']['confirmed']);
        $this->assertSame('mismatch', $rows['tax_type']['status']);
    }

    public function test_older_submissions_without_a_tax_type_have_no_tax_row(): void
    {
        $order = $this->order(['kyc_confirmed_data' => ['tin' => '123-456-789-000', 'registered_name' => 'X', 'registered_address' => 'Y']]);

        $this->assertArrayNotHasKey('tax_type', collect((new KycReviewService)->rows($order))->keyBy('key'));
    }

    public function test_unreadable_documents_are_reported_as_not_read(): void
    {
        $order = $this->order(['kyc_ocr_data' => ['bir' => ['tin' => null, 'registered_name' => null, 'registered_address' => null, 'expiry_date' => null]]]);

        $rows = collect((new KycReviewService)->rows($order))->keyBy('key');

        $this->assertSame('unread', $rows['tin']['status']);
        $this->assertSame('unread', $rows['bir_expiry_date']['status']);
    }

    public function test_orders_without_kyc_data_produce_no_review(): void
    {
        $this->assertSame([], (new KycReviewService)->rows(new Order(['shop_name' => 'X'])));
    }
}
