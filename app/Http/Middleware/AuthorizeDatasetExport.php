<?php

namespace App\Http\Middleware;

use App\Services\ShopDataExportService;
use Closure;
use Illuminate\Http\Request;

/** Requires view access to the module that owns the exported dataset. */
class AuthorizeDatasetExport
{
    public function __construct(private readonly CheckPermission $permission) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $module = ShopDataExportService::MODULES[$request->route('dataset')] ?? null;

        abort_if($module === null, 404);

        return $this->permission->handle($request, $next, $module, 'view');
    }
}
