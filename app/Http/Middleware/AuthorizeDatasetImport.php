<?php

namespace App\Http\Middleware;

use App\Services\ShopCsvImportService;
use App\Services\ShopLedgerCsvImportService;
use Closure;
use Illuminate\Http\Request;

/** Requires create access to the module that owns the dataset being imported. */
class AuthorizeDatasetImport
{
    public function __construct(private readonly CheckPermission $permission) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $dataset = $request->route('dataset');
        $module = (ShopCsvImportService::MODULES + ShopLedgerCsvImportService::MODULES)[$dataset] ?? null;

        abort_if($module === null, 404);
        abort_if(in_array($dataset, ShopLedgerCsvImportService::OWNER_ONLY, true) && $request->user()?->role !== 'owner', 403);

        return $this->permission->handle($request, $next, $module, 'create');
    }
}
