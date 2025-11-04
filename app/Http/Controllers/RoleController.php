<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\JsonService;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    protected $jsonService;

    public function __construct()
    {
        $this->jsonService = new JsonService();
    }

    public function index()
    {
        if (request()->ajax()) {
            $data = Role::with('permissions')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('permissions', function ($row) {
                    return $row->permissions->pluck('name')->toArray(); // return as array for JS to map
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $permissions = Permission::all();

        return view('roles.index', compact('permissions'));
    }

    public function create()
    {
        return view('roles.create', [
            'permissions' => Permission::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,'.$request->id,
        ]);

        $newPermissions = array_filter($request->new_permissions ?? []);

        foreach ($newPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $allPermissions = array_merge($request->permissions ?? [], $newPermissions);

        if ($request->id) {
            $role = Role::findOrFail($request->id);
            $role->update(['name' => $request->name]);
            $role->syncPermissions($allPermissions);

            return $this->jsonService->sendResponse(
                true,
                null,
                'Role updated successfully!',
                Response::HTTP_OK
            );
        }
        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($allPermissions);

        return $this->jsonService->sendResponse(
            true,
            null,
            'Role created successfully!',
            Response::HTTP_CREATED
        );

    }

    public function edit($id)
    {
        try {
            $role = Role::with('permissions')->findOrFail($id);

            return $this->jsonService->sendResponse(
                true,
                [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name'),
                ],
                'Role fetched successfully',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->jsonService->sendResponse(
                false,
                null,
                'Role not found',
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);
            $role->delete();

            return $this->jsonService->sendResponse(
                true,
                null,
                'Role deleted successfully!',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->jsonService->sendResponse(
                false,
                null,
                'Failed to delete role',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
