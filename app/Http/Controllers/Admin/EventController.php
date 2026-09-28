<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Paginated event list with search, status filter and sorting.
     */
    public function index(Request $request): View
    {
        $query = Event::query()->with('category');

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->search($search);
        }

        $status = (string) $request->query('status', '');
        if (in_array($status, [Event::STATUS_UPCOMING, Event::STATUS_ONGOING, Event::STATUS_COMPLETED, Event::STATUS_CANCELLED], true)) {
            $query->where('status', $status);
        }

        $type = (string) $request->query('type', '');
        if (in_array($type, Event::TYPES, true)) {
            $query->where('event_type', $type);
        }

        $sortOptions = [
            'upcoming' => 'Soonest first',
            'newest' => 'Newest first',
            'name' => 'Name A → Z',
            'views' => 'Most viewed',
        ];

        $sort = (string) $request->query('sort', 'upcoming');
        if (! isset($sortOptions[$sort])) {
            $sort = 'upcoming';
        }

        match ($sort) {
            'newest' => $query->orderBy('created_at', 'desc'),
            'name' => $query->orderBy('name', 'asc'),
            'views' => $query->orderByDesc('views_count'),
            default => $query->orderBy('start_date', 'asc'),
        };

        $items = $query->paginate(25)->withQueryString();

        return view('admin.events.index', [
            'items' => $items,
            'search' => $search,
            'status' => $status,
            'type' => $type,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
            'eventTypes' => Event::TYPES,
        ]);
    }

    /**
     * Blank form for a new event.
     */
    public function create(): View
    {
        return view('admin.events.form', [
            'event' => new Event,
            'formTitle' => 'Add event',
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(['id', 'name']),
            'eventTypes' => Event::TYPES,
        ]);
    }

    /**
     * Persist a new event.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateEvent($request);

        $event = new Event;
        $event->fill($this->mapFields($request, $validated));
        $event->slug = $this->uniqueSlug($event->name);
        $event->save();

        return redirect()
            ->route('admin.events.edit', $event)
            ->with('success', 'Event "'.$event->name.'" created.');
    }

    /**
     * Edit form for an existing event.
     */
    public function edit(Event $event): View
    {
        return view('admin.events.form', [
            'event' => $event,
            'formTitle' => 'Edit event',
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(['id', 'name']),
            'eventTypes' => Event::TYPES,
        ]);
    }

    /**
     * Update an event (slug follows a name change).
     */
    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $this->validateEvent($request);
        $originalName = (string) $event->getOriginal('name');

        $event->fill($this->mapFields($request, $validated));

        if ($event->name !== $originalName) {
            $event->slug = $this->uniqueSlug($event->name, $event->id);
        }

        $event->save();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event "'.$event->name.'" updated.');
    }

    /**
     * Delete an event and its cover artwork.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $name = $event->name;

        if ($event->cover_image) {
            Storage::disk('public')->delete($event->cover_image);
        }

        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event "'.$name.'" deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateEvent(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'category_id' => ['required', 'exists:categories,id'],
            'event_type' => ['required', 'in:'.implode(',', Event::TYPES)],
            'description' => ['nullable', 'string', 'max:4000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:190'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric', 'min:-90', 'max:90'],
            'longitude' => ['nullable', 'numeric', 'min:-180', 'max:180'],
            'ticket_url' => ['nullable', 'url', 'max:500'],
            'status' => ['required', 'in:'.implode(',', [Event::STATUS_UPCOMING, Event::STATUS_ONGOING, Event::STATUS_COMPLETED, Event::STATUS_CANCELLED])],
            'featured' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function mapFields(Request $request, array $validated): array
    {
        $fields = [
            'name' => $validated['name'],
            'category_id' => (int) $validated['category_id'],
            'event_type' => $validated['event_type'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'time' => $validated['time'] ?? null,
            'venue' => $validated['venue'] ?? null,
            'city' => $validated['city'] ?? null,
            'country' => $validated['country'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'ticket_url' => $validated['ticket_url'] ?? null,
            'status' => $validated['status'],
            'featured' => $request->boolean('featured'),
        ];

        if ($request->hasFile('cover_image')) {
            $fields['cover_image'] = $this->storeUploadedImage($request->file('cover_image'), 'events');
        }

        return $fields;
    }

    /**
     * Slug derived from the name, made unique against the table.
     */
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'event-'.Str::lower(Str::random(6));

        $slug = $base;
        $suffix = 2;

        while (
            Event::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($builder) => $builder->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * Store a validated image upload on the public disk and downscale it to 1600px wide.
     */
    private function storeUploadedImage(?UploadedFile $file, string $directory = 'events'): ?string
    {
        if ($file === null || ! $file->isValid()) {
            return null;
        }

        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->guessExtension()));
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return null;
        }

        $filename = now()->format('Ymd_His').'_'.Str::random(8).'.'.$extension;
        $path = $file->storeAs($directory, $filename, 'public');

        if ($path === false) {
            return null;
        }

        $this->resizeImage($path);

        return $path;
    }

    /**
     * Best-effort GD downscale so admin uploads stay lightweight.
     */
    private function resizeImage(string $path, int $maxWidth = 1600): void
    {
        try {
            $full = Storage::disk('public')->path($path);
            $info = @getimagesize($full);

            if ($info === false || $info[0] <= $maxWidth) {
                return;
            }

            $height = (int) max(1, round($info[1] * ($maxWidth / $info[0])));
            $source = match ($info[2]) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($full),
                IMAGETYPE_PNG => imagecreatefrompng($full),
                IMAGETYPE_WEBP => @imagecreatefromwebp($full),
                default => null,
            };

            if ($source === null) {
                return;
            }

            $canvas = imagecreatetruecolor($maxWidth, $height);

            if (in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
            }

            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $maxWidth, $height, $info[0], $info[1]);

            match ($info[2]) {
                IMAGETYPE_JPEG => imagejpeg($canvas, $full, 85),
                IMAGETYPE_PNG => imagepng($canvas, $full, 6),
                default => imagewebp($canvas, $full, 85),
            };

            imagedestroy($source);
            imagedestroy($canvas);
        } catch (\Throwable) {
            // Keep the original upload when GD cannot process it.
        }
    }
}
