<?php

namespace Tests\Unit;

use App\Services\Ocr\KycFieldExtractor;
use PHPUnit\Framework\TestCase;

class KycFieldExtractorTest extends TestCase
{
    public function test_extracts_tin_name_and_address_from_bir_style_text(): void
    {
        $text = "CERTIFICATE OF REGISTRATION\nTIN: 123-456-789-000\nRegistered Name: SUDS & BUBBLES LAUNDRY\nRegistered Address: 12 Rizal St, Brgy. Poblacion, Imus, Cavite\nTax Type: Income Tax";

        $result = (new KycFieldExtractor)->extract($text);

        $this->assertSame('123-456-789-000', $result['tin']);
        $this->assertSame('SUDS & BUBBLES LAUNDRY', $result['registered_name']);
        $this->assertSame('12 Rizal St, Brgy. Poblacion, Imus, Cavite', $result['registered_address']);
    }

    public function test_address_continues_on_the_next_line(): void
    {
        $text = "Business Name: Clean Co\nBusiness Address: 5 Mabini Ave\nBacoor City, Cavite\nOwner: Juan";

        $this->assertSame('5 Mabini Ave, Bacoor City, Cavite', (new KycFieldExtractor)->extract($text)['registered_address']);
    }

    public function test_a_title_line_is_not_mistaken_for_the_name_label(): void
    {
        $text = 'BUSINESS NAME REGISTRATION CERTIFICATE
Business Name: LAUNDYMOT';

        $this->assertSame('LAUNDYMOT', (new KycFieldExtractor)->extract($text)['registered_name']);
    }

    public function test_reads_the_tax_type_from_the_certificate(): void
    {
        $extract = fn (string $t) => (new KycFieldExtractor)->extract($t)['tax_type'];

        $this->assertSame('vat', $extract("TIN: 123-456-789-000\nTax Type: Value Added Tax\nIncome Tax"));
        $this->assertSame('non_vat', $extract("Tax Types:\nPercentage Tax\nIncome Tax"));
        $this->assertSame('non_vat', $extract('Registered as Non-VAT'));
        $this->assertNull($extract("Value Added Tax\nPercentage Tax"), 'Both listed: ambiguous.');
        $this->assertNull($extract('Income Tax only'));
    }

    public function test_returns_nulls_when_nothing_is_found(): void
    {
        $this->assertSame(
            ['tin' => null, 'registered_name' => null, 'registered_address' => null, 'tax_type' => null],
            (new KycFieldExtractor)->extract('unreadable smudge'),
        );
    }
}
