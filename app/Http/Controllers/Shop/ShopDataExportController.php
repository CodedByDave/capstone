<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\ShopContext;
use App\Services\ShopDataExportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShopDataExportController extends Controller
{
    public function __construct(
        private readonly ShopDataExportService $exports,
        private readonly ShopContext $context,
    ) {}

    /** One dataset as CSV. The route only matches known datasets and applies the module permission. */
    public function show(string $dataset): StreamedResponse
    {
        $shopId = $this->context->shopIdFor(auth()->user());
        $this->exports->logExport($shopId, $dataset);

        return response()->streamDownload(function () use ($shopId, $dataset) {
            $out = fopen('php://output', 'w');
            $this->exports->writeCsv($shopId, $dataset, $out);
            fclose($out);
        }, $this->exports->filename($dataset), ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Every dataset as a zip of CSV files (shop owner only). */
    public function archive(): BinaryFileResponse
    {
        $shopId = $this->context->shopIdFor(auth()->user());
        $this->exports->logExport($shopId, 'all');

        return response()
            ->download($this->exports->archive($shopId), 'shop-data-'.now()->format('Y-m-d').'.zip', ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend();
    }
}
