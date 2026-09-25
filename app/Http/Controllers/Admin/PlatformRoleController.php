<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeletePlatformRoleRequest;
use App\Http\Requests\Admin\StorePlatformRoleRequest;
use App\Http\Requests\Admin\TogglePlatformPermissionRequest;
use App\Models\PlatformRole;
use App\Services\PlatformRoleService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PlatformRoleController extends Controller
{
    public function __construct(
        private readonly PlatformRoleService $service,
    ) {}

    public function index(): Response
    {
        return Inertia::render(
            'admin/settings/RolesPermissions',
            $this->service->managementData(),
        );
    }

    public function store(StorePlatformRoleRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->createRole($request->validated()),
            201,
        );
    }

    public function togglePermission(
        TogglePlatformPermissionRequest $request,
        PlatformRole $platformRole,
    ): JsonResponse {
        return response()->json([
            'enabled' => $this->service->togglePermission(
                $platformRole,
                $request->validated('permission'),
            ),
        ]);
    }

    public function destroy(
        DeletePlatformRoleRequest $request,
        PlatformRole $platformRole,
    ): JsonResponse {
        $this->service->deleteRole($platformRole);

        return response()->json(['success' => true]);
    }
}
