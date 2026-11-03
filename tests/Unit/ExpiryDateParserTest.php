<?php

namespace Tests\Unit;

use App\Services\Ocr\ExpiryDateParser;
use PHPUnit\Framework\TestCase;

class ExpiryDateParserTest extends TestCase
{
    public function test_reads_long_form_date_after_keyword(): void
    {
        $text = "BUSINESS PERMIT\nIssued: January 5, 2026\nValid until December 31, 2026";

        $this->assertSame('2026-12-31', (new ExpiryDateParser)->parse($text));
    }

    public function test_takes_end_of_a_validity_range(): void
    {
        $text = 'Valid from 03/15/2024 to 03/15/2029';

        $this->assertSame('2029-03-15', (new ExpiryDateParser)->parse($text));
    }

    public function test_reads_day_month_year_and_iso_formats(): void
    {
        $parser = new ExpiryDateParser;

        $this->assertSame('2027-06-30', $parser->parse('Expiry: 30 June 2027'));
        $this->assertSame('2027-06-30', $parser->parse('Expires 2027-06-30'));
    }

    public function test_falls_back_to_latest_date_without_keyword(): void
    {
        $this->assertSame('2028-01-10', (new ExpiryDateParser)->parse('Registered 01/10/2025 ... 01/10/2028'));
    }

    public function test_returns_null_when_no_date_found(): void
    {
        $this->assertNull((new ExpiryDateParser)->parse('Certificate of Registration'));
    }
}
