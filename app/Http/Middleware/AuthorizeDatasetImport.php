<?php

namespace App\Http\Middleware;

use App\Services\ShopCsvImportService;
use Closure;
use Illuminate\Http\Request;

/** Requires create access to the module that owns the dataset being imported. */
class AuthorizeDatasetImport
{
    public function __construct(private readonly CheckPermission $permission) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $module = ShopCsvImportService::MODULES[$request->route('dataset')] ?? null;

        abort_if($module === null, 404);

        return $this->permission->handle($request, $next, $module, 'create');
    }
}
