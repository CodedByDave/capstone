<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ImportShopCsvRequest;
use App\Services\ShopContext;
use App\Services\ShopCsvImportService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShopDataImportController extends Controller
{
    public function __construct(
        private readonly ShopCsvImportService $imports,
        private readonly ShopContext $context,
    ) {}

    /** A header row plus one example row for the dataset. */
    public function template(string $dataset): StreamedResponse
    {
        $rows = $this->imports->templateRows($dataset);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            foreach ($rows as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, $this->imports->templateFilename($dataset), ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function store(ImportShopCsvRequest $request, string $dataset): JsonResponse
    {
        $shopId = $this->context->shopIdFor(auth()->user());
        $result = $this->imports->import($shopId, $dataset, $request->file('csv_file'));

        if ($result['errors'] !== []) {
            return response()->json(['errors' => $result['errors']], 422);
        }

        return response()->json(['message' => "{$result['imported']} record(s) imported.", 'imported' => $result['imported']]);
    }
}
