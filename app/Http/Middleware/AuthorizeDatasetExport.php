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
        abort_if(in_array($request->route('dataset'), ShopDataExportService::OWNER_ONLY, true) && $request->user()?->role !== 'owner', 403);

        return $this->permission->handle($request, $next, $module, 'view');
    }
}
