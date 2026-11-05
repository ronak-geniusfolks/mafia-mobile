<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JsonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    protected $jsonService;

    public function __construct()
    {
        $this->jsonService = new JsonService();
    }

    public function index()
    {
        if (request()->ajax()) {
            $data = User::with('roles.permissions')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('avatar', function ($user) {
                    return $user->avatar_url ?? asset('assets/images/users/user-5.jpg'); // customize
                })
                ->addColumn('roles', function ($user) {
                    return $user->roles->map(function ($role) {
                        return [
                            'name' => $role->name,
                            'permissions' => $role->permissions->pluck('name')->toArray(),
                        ];
                    })->values()->toArray(); // Ensure it's a plain array for JSON
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $roles = Role::all();

        return view('users.index', compact('roles'));
    }

    public function create()
    {
        return view('users.create', ['roles' => Role::all()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$request->id,
            'user_name' => 'required|string|unique:users,user_name,'.$request->id,
            'roles' => 'required|array',
            'password' => $request->id ? 'nullable|min:6|confirmed' : 'required|min:6|confirmed',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle avatar upload
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->id) {
            // Update existing user
            $user = User::findOrFail($request->id);
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'user_name' => $request->user_name,
                'avatar' => $avatarPath ?? $user->avatar,
            ]);

            if ($request->filled('password')) {
                $user->update(['password' => bcrypt($request->password)]);
            }

            $user->syncRoles($request->roles);

            return response()->json([
                'status' => true,
                'message' => 'User updated successfully!',
            ]);
        }
        // Create new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'user_name' => $request->user_name,
            'password' => bcrypt($request->password),
            'avatar' => $avatarPath,
        ]);

        $user->assignRole($request->roles);

        return response()->json([
            'status' => true,
            'message' => 'User created successfully!',
        ]);

    }

    public function edit($id)
    {
        try {
            $user = User::with('roles')->findOrFail($id);

            return $this->jsonService->sendResponse(
                true,
                [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_name' => $user->user_name,
                    'roles' => $user->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                        ];
                    }),
                    'avatar_url' => $user->avatar
                    ? asset('storage/'.$user->avatar)
                    : asset('assets/images/users/user-5.jpg'),
                ],
                'User fetched successfully',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->jsonService->sendResponse(
                false,
                null,
                'User not found',
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Optional: Delete avatar file from storage
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->delete();

            return $this->jsonService->sendResponse(
                true,
                null,
                'User deleted successfully!',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->jsonService->sendResponse(
                false,
                null,
                'Failed to delete user',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
