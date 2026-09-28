<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Models\UserPreference;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends AdminApiController
{
    /**
     * GET /api/admin/users — member directory (never exposes password hashes).
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        $this->applySearch($request, $query, ['name', 'email']);

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $this->applyStatus($request, $query);

        $query->orderByDesc('id');

        $paginator = $query->paginate($this->perPage($request, 15))->withQueryString();

        return response()->json(
            ApiSerializer::page($paginator, fn (User $user): array => ApiSerializer::user($user))
        );
    }

    /**
     * POST /api/admin/users — create a member.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['sometimes', 'string', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'status' => ['sometimes', 'string', Rule::in([User::STATUS_ACTIVE, User::STATUS_SUSPENDED])],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'verified' => ['sometimes', 'boolean'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? User::ROLE_USER,
            'status' => $data['status'] ?? User::STATUS_ACTIVE,
            'bio' => $data['bio'] ?? null,
            'avatar' => $this->uploadImage($request, 'avatar'),
            'email_verified_at' => ($data['verified'] ?? false) ? now() : null,
        ]);

        UserPreference::query()->create(
            array_merge(UserPreference::defaults(), ['user_id' => $user->id])
        );

        return response()->json(['data' => ApiSerializer::user($user)], 201);
    }

    /**
     * PUT/PATCH /api/admin/users/{user} — update a member.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['sometimes', 'string', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'status' => ['sometimes', 'string', Rule::in([User::STATUS_ACTIVE, User::STATUS_SUSPENDED])],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'verified' => ['sometimes', 'boolean'],
        ]);

        if ($user->is($request->user()) && (($data['role'] ?? $user->role) !== User::ROLE_ADMIN)) {
            return response()->json(['message' => 'You cannot remove your own administrator role.'], 422);
        }

        $user->name = $data['name'] ?? $user->name;
        $user->email = $data['email'] ?? $user->email;
        $user->bio = array_key_exists('bio', $data) ? $data['bio'] : $user->bio;
        $user->role = $data['role'] ?? $user->role;
        $user->status = $data['status'] ?? $user->status;
        $user->avatar = $this->uploadImage($request, 'avatar', $user->avatar);

        if (array_key_exists('verified', $data)) {
            $user->email_verified_at = $data['verified'] ? ($user->email_verified_at ?? now()) : null;
        }

        if (filled($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return response()->json(['data' => ApiSerializer::user($user)]);
    }

    /**
     * DELETE /api/admin/users/{user} — soft delete a member.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->is($request->user())) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        if ($user->role === User::ROLE_ADMIN && User::query()->admins()->count() <= 1) {
            return response()->json(['message' => 'The last administrator cannot be deleted.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}
