<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Engagement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Paginated member directory with role/status filters.
     */
    public function index(Request $request): View
    {
        $query = User::query();

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->where(fn ($builder) => $builder->where('name', 'like', $like)
                ->orWhere('email', 'like', $like));
        }

        $role = (string) $request->query('role', '');
        if (in_array($role, [User::ROLE_ADMIN, User::ROLE_USER], true)) {
            $query->where('role', $role);
        }

        $status = (string) $request->query('status', '');
        if (in_array($status, [User::STATUS_ACTIVE, User::STATUS_SUSPENDED], true)) {
            $query->where('status', $status);
        }

        $sortOptions = [
            'newest' => 'Newest first',
            'oldest' => 'Oldest first',
            'name' => 'Name A → Z',
            'email' => 'Email A → Z',
        ];

        $sort = (string) $request->query('sort', 'newest');
        if (! isset($sortOptions[$sort])) {
            $sort = 'newest';
        }

        match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'name' => $query->orderBy('name', 'asc'),
            'email' => $query->orderBy('email', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $items = $query->paginate(25)->withQueryString();

        return view('admin.users.index', [
            'items' => $items,
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
        ]);
    }

    /**
     * Blank form for a new member.
     */
    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User,
            'formTitle' => 'Add member',
        ]);
    }

    /**
     * Persist a brand new member account.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in([User::ROLE_USER, User::ROLE_ADMIN])],
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_SUSPENDED])],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        Engagement::log(auth()->id(), 'profile_updated', 'Created member '.$user->name, User::class, $user->id);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', 'Member "'.$user->name.'" created.');
    }

    /**
     * Edit form for an existing member.
     */
    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'formTitle' => 'Edit member',
        ]);
    }

    /**
     * Update profile, role and status (password reset is optional).
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in([User::ROLE_USER, User::ROLE_ADMIN])],
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_SUSPENDED])],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $isSelf = $request->user()?->is($user) ?? false;

        if ($isSelf) {
            if ($validated['role'] !== User::ROLE_ADMIN || $validated['status'] !== User::STATUS_ACTIVE) {
                return back()->with('error', 'You cannot remove your own admin role or suspend your own account.');
            }
        }

        $password = $validated['password'] ?? null;
        unset($validated['password']);

        $user->fill($validated);

        if ($password !== null) {
            $user->password = Hash::make($password);
        }

        $user->save();

        Engagement::log($request->user()?->id, 'profile_updated', 'Updated member '.$user->name, User::class, $user->id);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Member "'.$user->name.'" updated.');
    }

    /**
     * Soft delete a member (never yourself).
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->is($user) ?? false) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        Engagement::log($request->user()?->id, 'profile_updated', 'Deleted member '.$name, User::class, $user->id);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Member "'.$name.'" deleted.');
    }
}
