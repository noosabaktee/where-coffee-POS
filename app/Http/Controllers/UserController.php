<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $users = User::query()->with(['outlet', 'roles', 'permissions'])->orderBy('name')->get();
        return response()->json(UserResource::collection($users)->resolve($request));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);
        $user = DB::transaction(function () use ($request): User {
            $data = $request->safe()->except('role');
            if ($request->input('role') === 'Administrator') {
                $data['outlet_id'] = null;
            }
            $user = User::query()->create($data);
            $user->assignRole($request->input('role'));
            return $user;
        });

        return response()->json([
            'message' => 'Pengguna berhasil ditambahkan.',
            'data' => (new UserResource($user->load(['outlet', 'roles', 'permissions'])))->resolve($request),
        ], 201);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);
        DB::transaction(function () use ($request, $user): void {
            $data = $request->safe()->except(['role', 'password']);
            if ($request->filled('password')) {
                $data['password'] = $request->input('password');
            }
            $role = $request->input('role', $user->getRoleNames()->first());
            if ($role === 'Administrator') {
                $data['outlet_id'] = null;
            }
            $user->update($data);
            $user->syncRoles([$role]);
        });

        return response()->json([
            'message' => 'Pengguna berhasil diperbarui.',
            'data' => (new UserResource($user->fresh()->load(['outlet', 'roles', 'permissions'])))->resolve($request),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        if ($user->transactions()->exists() || $user->expenses()->exists()) {
            $user->update(['is_active' => false]);
            return response()->json(['message' => 'Pengguna memiliki riwayat aktivitas dan telah dinonaktifkan.']);
        }
        if ($user->hasRole('Administrator') && User::role('Administrator')->count() <= 1) {
            throw ValidationException::withMessages(['user' => 'Administrator terakhir tidak dapat dihapus.']);
        }
        $user->delete();
        return response()->json(['message' => 'Pengguna berhasil dihapus.']);
    }
}
