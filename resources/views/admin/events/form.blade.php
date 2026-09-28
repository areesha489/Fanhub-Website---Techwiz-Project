@extends('layouts.admin')

@section('title', $formTitle.' · Fan Hub+ Admin')

@section('content')
    <div class="fh-page-head">
        <div>
            <h1 class="fh-page-title">{{ $formTitle }}</h1>
            <p class="fh-page-sub">Schedule, venue, coordinates and ticketing for this gathering.</p>
        </div>
        <a class="btn btn-fh-ghost" href="{{ route('admin.events.index') }}">
            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Back to events
        </a>
    </div>

    <div class="fh-card fh-card-pad" style="max-width: 960px;">
        @include('admin.partials.form-errors')

        <form
            method="POST"
            action="{{ $event->exists ? route('admin.events.update', $event) : route('admin.events.store') }}"
            enctype="multipart/form-data"
        >
            @csrf
            @if($event->exists)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <x-admin.form-row label="Name" for="name" required :error="$errors->first('name')">
                        <input class="fh-field" type="text" id="name" name="name" value="{{ old('name', $event->name) }}" maxlength="190" required>
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-4">
                    <x-admin.form-row label="Category" for="category_id" required :error="$errors->first('category_id')">
                        <select class="fh-field" id="category_id" name="category_id" required>
                            <option value="">Choose…</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id', $event->category_id) === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </x-admin.form-row>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <x-admin.select
                        name="event_type"
                        label="Event type"
                        :options="collect($eventTypes)->mapWithKeys(fn ($t) => [$t => $t])->all()"
                        :selected="old('event_type', $event->event_type ?? 'Convention')"
                        required
                        :error="$errors->first('event_type')"
                    />
                </div>

                <div class="col-12 col-md-4">
                    <x-admin.select
                        name="status"
                        label="Status"
                        :options="['upcoming' => 'Upcoming', 'ongoing' => 'Ongoing', 'completed' => 'Completed', 'cancelled' => 'Cancelled']"
                        :selected="old('status', $event->status ?? 'upcoming')"
                        required
                        :error="$errors->first('status')"
                    />
                </div>

                <div class="col-12 col-md-4">
                    <x-admin.form-row label="Featured" for="featured" help="Promoted on the home page">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="featured" name="featured" value="1" @checked(old('featured', $event->featured))>
                            <label class="form-check-label fh-help" for="featured">Featured event</label>
                        </div>
                    </x-admin.form-row>
                </div>
            </div>

            <x-admin.form-row label="Description" for="description" :error="$errors->first('description')">
                <textarea class="fh-field" id="description" name="description" rows="5" maxlength="4000">{{ old('description', $event->description) }}</textarea>
            </x-admin.form-row>

            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Start date" for="start_date" required :error="$errors->first('start_date')">
                        <input class="fh-field" type="date" id="start_date" name="start_date" value="{{ old('start_date', optional($event->start_date)->format('Y-m-d')) }}" required>
                    </x-admin.form-row>
                </div>

                <div class="col-6 col-md-3">
                    <x-admin.form-row label="End date" for="end_date" :error="$errors->first('end_date')">
                        <input class="fh-field" type="date" id="end_date" name="end_date" value="{{ old('end_date', optional($event->end_date)->format('Y-m-d')) }}">
                    </x-admin.form-row>
                </div>

                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Start time" for="time" :error="$errors->first('time')">
                        <input class="fh-field" type="time" id="time" name="time" value="{{ old('time', $event->time) }}">
                    </x-admin.form-row>
                </div>

                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Ticket URL" for="ticket_url" :error="$errors->first('ticket_url')">
                        <input class="fh-field" type="url" id="ticket_url" name="ticket_url" value="{{ old('ticket_url', $event->ticket_url) }}" maxlength="500" placeholder="https://…">
                    </x-admin.form-row>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <x-admin.form-row label="Venue" for="venue" :error="$errors->first('venue')">
                        <input class="fh-field" type="text" id="venue" name="venue" value="{{ old('venue', $event->venue) }}" maxlength="190">
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-4">
                    <x-admin.form-row label="City" for="city" :error="$errors->first('city')">
                        <input class="fh-field" type="text" id="city" name="city" value="{{ old('city', $event->city) }}" maxlength="120">
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-4">
                    <x-admin.form-row label="Country" for="country" :error="$errors->first('country')">
                        <input class="fh-field" type="text" id="country" name="country" value="{{ old('country', $event->country) }}" maxlength="120">
                    </x-admin.form-row>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Latitude" for="latitude" :error="$errors->first('latitude')" hint="-90 to 90">
                        <input class="fh-field" type="number" id="latitude" name="latitude" value="{{ old('latitude', $event->latitude) }}" step="0.0000001" min="-90" max="90" inputmode="decimal">
                    </x-admin.form-row>
                </div>

                <div class="col-6 col-md-3">
                    <x-admin.form-row label="Longitude" for="longitude" :error="$errors->first('longitude')" hint="-180 to 180">
                        <input class="fh-field" type="number" id="longitude" name="longitude" value="{{ old('longitude', $event->longitude) }}" step="0.0000001" min="-180" max="180" inputmode="decimal">
                    </x-admin.form-row>
                </div>

                <div class="col-12 col-md-6">
                    <x-admin.form-row label="Cover image" for="cover_image" :error="$errors->first('cover_image')" hint="jpg, png or webp — max 2 MB">
                        <input class="fh-field" type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/webp">
                    </x-admin.form-row>
                </div>
            </div>

            @if($event->cover_image)
                <div class="mb-3">
                    <img src="{{ asset('storage/'.ltrim($event->cover_image, '/')) }}" alt="Current cover for {{ $event->name }}" width="160" height="90" loading="lazy" decoding="async" class="fh-img rounded" style="object-fit: cover;">
                    <p class="fh-help mb-0">Current cover — upload a new file to replace it.</p>
                </div>
            @endif

            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-fh" type="submit">
                    <i class="bi bi-check-lg me-2" aria-hidden="true"></i>{{ $event->exists ? 'Save changes' : 'Create event' }}
                </button>
                <a class="btn btn-fh-ghost" href="{{ route('admin.events.index') }}">Cancel</a>
            </div>
        </form>

        @if($event->exists)
            <div class="fh-divider my-4" aria-hidden="true"></div>

            <form
                method="POST"
                action="{{ route('admin.events.destroy', $event) }}"
                data-confirm="Delete {{ $event->name }}? This cannot be undone."
            >
                @csrf
                @method('DELETE')
                <button class="btn btn-fh-danger" type="submit">
                    <i class="bi bi-trash me-2" aria-hidden="true"></i>Delete event
                </button>
            </form>
        @endif
    </div>
@endsection
