<?php

namespace App\Http\Controllers\SupplierPortal;

use App\Exceptions\SupplierPortalException;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureActiveSupplier;
use App\Http\Requests\SupplierPortal\ImportSupplierItemsRequest;
use App\Http\Requests\SupplierPortal\PreviewSupplierImportRequest;
use App\Services\SupplierItemImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Adding many supplies at once, from a CSV file or a scanned price list. */
class SupplierItemImportController extends Controller
{
    public function __construct(private readonly SupplierItemImportService $imports) {}

    /** Reads the file into rows for the supplier to review; nothing is saved yet. */
    public function preview(PreviewSupplierImportRequest $request): JsonResponse
    {
        $supplier = $this->supplier($request);

        try {
            $rows = $request->hasFile('csv_file')
                ? $this->imports->previewCsv($supplier, $request->file('csv_file'))
                : $this->imports->previewPhoto($supplier, $request->file('photo'));
        } catch (SupplierPortalException $exception) {
            $field = $request->hasFile('csv_file') ? 'csv_file' : 'photo';

            return response()->json(['message' => $exception->getMessage(), 'errors' => [$field => [$exception->getMessage()]]], 422);
        }

        return response()->json(['rows' => $rows]);
    }

    public function store(ImportSupplierItemsRequest $request): RedirectResponse
    {
        $result = $this->imports->import($this->supplier($request), $request->rows());

        $parts = array_filter([
            $result['added'] > 0 ? "{$result['added']} added" : null,
            $result['updated'] > 0 ? "{$result['updated']} updated" : null,
        ]);

        return back()->with('toast', ['type' => 'success', 'message' => 'Supplies saved: '.implode(', ', $parts).'.']);
    }

    public function template(): StreamedResponse
    {
        $rows = $this->imports->templateRows();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'supplies_import_template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function supplier(Request $request)
    {
        return $request->attributes->get(EnsureActiveSupplier::ATTRIBUTE);
    }
}
