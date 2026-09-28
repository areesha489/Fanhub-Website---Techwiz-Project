@extends('layouts.admin')

@section('title', $formTitle.' · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">{{ $formTitle }}</h1>
            <p class="fh-page-sub">
                @if($user->exists)
                    Update the account details, role and access status.
                @else
                    Create an account directly from the admin panel.
                @endif
            </p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('admin.users.index') }}">
            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Back to members
        </a>
    </div>

    <div class="fh-card fh-card-pad" style="max-width: 780px;">
        @include('admin.partials.form-errors')

        <form
            method="POST"
            action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
        >
            @csrf
            @if($user->exists)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Full name" for="name" required :error="$errors->first('name')">
                        <input
                            class="fh-field"
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            maxlength="120"
                            required
                            autocomplete="name"
                        >
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Email address" for="email" required :error="$errors->first('email')">
                        <input
                            class="fh-field"
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            maxlength="190"
                            required
                            autocomplete="email"
                        >
                    </x-admin.form-row>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <x-admin.select
                        name="role"
                        label="Role"
                        :options="['user' => 'Member', 'admin' => 'Admin']"
                        :selected="$user->role ?? 'user'"
                        required
                        :error="$errors->first('role')"
                        help="Admins can open this panel and manage every module."
                    />
                </div>

                <div class="col-12 col-md-6">
                    <x-admin.select
                        name="status"
                        label="Status"
                        :options="['active' => 'Active', 'suspended' => 'Suspended']"
                        :selected="$user->status ?? 'active'"
                        required
                        :error="$errors->first('status')"
                        help="Suspended members are signed out and blocked from logging in."
                    />
                </div>
            </div>

            <x-admin.form-row
                label="{{ $user->exists ? 'New password (optional)' : 'Password' }}"
                for="password"
                :required="! $user->exists"
                :error="$errors->first('password')"
                :help="$user->exists ? 'Leave blank to keep the current password.' : 'Minimum 8 characters — will be hashed before storage.'"
            >
                <input
                    class="fh-field"
                    type="password"
                    id="password"
                    name="password"
                    maxlength="200"
                    autocomplete="new-password"
                    {{ $user->exists ? '' : 'required' }}
                >
            </x-admin.form-row>

            <x-admin.form-row label="Confirm password" for="password_confirmation" :required="! $user->exists" :error="$errors->first('password_confirmation')">
                <input
                    class="fh-field"
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    maxlength="200"
                    autocomplete="new-password"
                    {{ $user->exists ? '' : 'required' }}
                >
            </x-admin.form-row>

            <x-admin.form-row label="Bio" for="bio" :error="$errors->first('bio')" hint="Optional, shown on the member profile">
                <textarea class="fh-field" id="bio" name="bio" rows="4" maxlength="1000">{{ old('bio', $user->bio) }}</textarea>
            </x-admin.form-row>

            @if($user->exists)
                <div class="fh-divider mb-3" aria-hidden="true"></div>
                <p class="fh-help mb-3">
                    Signed up {{ $user->created_at?->format('d M Y H:i') }}
                    @if($user->email_verified_at)
                        · verified {{ $user->email_verified_at->format('d M Y') }}
                    @else
                        · not verified yet
                    @endif
                </p>
            @endif

            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-fh" type="submit">
                    <i class="bi bi-check-lg me-2" aria-hidden="true"></i>{{ $user->exists ? 'Save changes' : 'Create member' }}
                </button>
                <a class="btn btn-fh-ghost" href="{{ route('admin.users.index') }}">Cancel</a>
            </div>
        </form>

        @if($user->exists && ! auth()->user()?->is($user))
            <div class="fh-divider my-4" aria-hidden="true"></div>

            <form
                method="POST"
                action="{{ route('admin.users.destroy', $user) }}"
                data-confirm="Delete member {{ $user->name }}? This cannot be undone."
            >
                @csrf
                @method('DELETE')
                <button class="btn btn-fh-danger" type="submit">
                    <i class="bi bi-trash me-2" aria-hidden="true"></i>Delete member
                </button>
            </form>
        @endif
    </div>
@endsection
