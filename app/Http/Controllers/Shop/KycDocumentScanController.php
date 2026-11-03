<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ScanKycDocumentRequest;
use App\Services\KycDocumentScanService;
use Illuminate\Http\JsonResponse;

class KycDocumentScanController extends Controller
{
    public function __construct(private KycDocumentScanService $scanner) {}

    public function scan(ScanKycDocumentRequest $request): JsonResponse
    {
        return response()->json(
            $this->scanner->scan($request->user()->id, $request->file('document'))
        );
    }
}
