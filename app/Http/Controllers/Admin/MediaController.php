<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Paginated multimedia list with search, type/status filters and sorting.
     */
    public function index(Request $request): View
    {
        $query = Media::query()->with('category');

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->search($search);
        }

        $type = (string) $request->query('type', '');
        if (in_array($type, Media::TYPES, true)) {
            $query->where('media_type', $type);
        }

        $status = (string) $request->query('status', '');
        if (in_array($status, [Media::STATUS_DRAFT, Media::STATUS_PUBLISHED, Media::STATUS_ARCHIVED], true)) {
            $query->where('status', $status);
        }

        $sortOptions = [
            'newest' => 'Newest first',
            'oldest' => 'Oldest first',
            'title' => 'Title A → Z',
            'views' => 'Most viewed',
            'rating' => 'Highest rated',
        ];

        $sort = (string) $request->query('sort', 'newest');
        if (! isset($sortOptions[$sort])) {
            $sort = 'newest';
        }

        match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'title' => $query->orderBy('title', 'asc'),
            'views' => $query->orderByDesc('views_count'),
            'rating' => $query->orderByDesc('rating'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $items = $query->paginate(25)->withQueryString();

        return view('admin.media.index', [
            'items' => $items,
            'search' => $search,
            'type' => $type,
            'status' => $status,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
        ]);
    }

    /**
     * Blank form for a new media entry.
     */
    public function create(): View
    {
        return view('admin.media.form', [
            'media' => new Media,
            'formTitle' => 'Add media',
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Persist a new media entry.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMedia($request);

        $media = new Media;
        $media->fill($this->mapFields($request, $validated));
        $media->slug = $this->uniqueSlug($media->title);
        $media->created_by = $request->user()?->id;
        $media->save();

        return redirect()
            ->route('admin.media.edit', $media)
            ->with('success', 'Media "'.$media->title.'" created.');
    }

    /**
     * Edit form for an existing media entry.
     */
    public function edit(Media $medium): View
    {
        return view('admin.media.form', [
            'media' => $medium,
            'formTitle' => 'Edit media',
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update a media entry (slug follows a title change).
     */
    public function update(Request $request, Media $medium): RedirectResponse
    {
        $validated = $this->validateMedia($request);
        $originalTitle = (string) $medium->getOriginal('title');

        $medium->fill($this->mapFields($request, $validated, $medium->url));

        if ($medium->title !== $originalTitle) {
            $medium->slug = $this->uniqueSlug($medium->title, $medium->id);
        }

        $medium->save();

        return redirect()
            ->route('admin.media.index')
            ->with('success', 'Media "'.$medium->title.'" updated.');
    }

    /**
     * Delete a media entry and its uploads.
     */
    public function destroy(Media $medium): RedirectResponse
    {
        $title = $medium->title;

        foreach (array_filter([$medium->thumbnail]) as $image) {
            Storage::disk('public')->delete($image);
        }

        foreach ((array) $medium->gallery as $image) {
            if (is_string($image)) {
                Storage::disk('public')->delete($image);
            }
        }

        $this->deleteStoredVideo($medium->url);

        $medium->delete();

        return redirect()
            ->route('admin.media.index')
            ->with('success', 'Media "'.$title.'" deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateMedia(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'category_id' => ['required', 'exists:categories,id'],
            'media_type' => ['required', 'in:'.implode(',', Media::TYPES)],
            'description' => ['nullable', 'string', 'max:3000'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'url' => ['nullable', 'string', 'max:500'],
            'video' => ['nullable', 'file', 'mimes:mp4,webm,mov', 'max:51200'],
            'remove_video' => ['nullable', 'boolean'],
            'embed_url' => ['nullable', 'string', 'max:500'],
            'duration' => ['nullable', 'string', 'max:20'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_gallery' => ['nullable', 'array'],
            'remove_gallery.*' => ['string'],
            'status' => ['required', 'in:'.implode(',', [Media::STATUS_DRAFT, Media::STATUS_PUBLISHED, Media::STATUS_ARCHIVED])],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'featured' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function mapFields(Request $request, array $validated, ?string $currentUrl = null): array
    {
        $url = $validated['url'] ?? null;

        if ($request->hasFile('video')) {
            $stored = $this->storeUploadedVideo($request->file('video'));

            if ($stored !== null) {
                $this->deleteStoredVideo($currentUrl);
                $url = $stored;
            }
        } elseif ($request->boolean('remove_video')) {
            $this->deleteStoredVideo($currentUrl);
        } elseif ($this->isStoredVideo($currentUrl) && ($url === null || $url === '')) {
            $url = $currentUrl;
        } elseif ($this->isStoredVideo($currentUrl) && $url !== $currentUrl) {
            $this->deleteStoredVideo($currentUrl);
        }

        $fields = [
            'title' => $validated['title'],
            'category_id' => (int) $validated['category_id'],
            'media_type' => $validated['media_type'],
            'description' => $validated['description'] ?? null,
            'url' => $url,
            'embed_url' => $validated['embed_url'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'status' => $validated['status'],
            'rating' => $validated['rating'] ?? 0,
            'featured' => $request->boolean('featured'),
        ];

        if ($request->hasFile('thumbnail')) {
            $fields['thumbnail'] = $this->storeUploadedImage($request->file('thumbnail'), 'media');
        }

        $gallery = array_values(array_filter((array) ($request->input('_current_gallery') ?? []), 'is_string'));

        foreach ((array) $request->input('remove_gallery', []) as $remove) {
            $gallery = array_values(array_filter($gallery, fn (string $path) => $path !== $remove));
        }

        if ($request->hasFile('gallery')) {
            foreach ((array) $request->file('gallery') as $upload) {
                if ($upload instanceof UploadedFile && $upload->isValid()) {
                    $stored = $this->storeUploadedImage($upload, 'media/gallery');
                    if ($stored !== null) {
                        $gallery[] = $stored;
                    }
                }
            }
        }

        if ($request->hasAny(['gallery', 'remove_gallery'])) {
            $fields['gallery'] = array_values(array_unique($gallery));
        }

        return $fields;
    }

    /**
     * Slug derived from the title, made unique against the table.
     */
    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'media-'.Str::lower(Str::random(6));

        $slug = $base;
        $suffix = 2;

        while (
            Media::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($builder) => $builder->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * True when the stored source is a video uploaded through this form.
     */
    private function isStoredVideo(?string $url): bool
    {
        return is_string($url) && str_starts_with($url, 'media/videos/');
    }

    /**
     * Delete an uploaded video from the public disk. External links are left alone.
     */
    private function deleteStoredVideo(?string $url): void
    {
        if ($this->isStoredVideo($url)) {
            Storage::disk('public')->delete($url);
        }
    }

    /**
     * Store a video upload on the public disk.
     */
    private function storeUploadedVideo(?UploadedFile $file): ?string
    {
        if ($file === null || ! $file->isValid()) {
            return null;
        }

        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->guessExtension()));

        if (! in_array($extension, ['mp4', 'webm', 'mov'], true)) {
            return null;
        }

        $filename = now()->format('Ymd_His').'_'.Str::random(8).'.'.$extension;
        $path = $file->storeAs('media/videos', $filename, 'public');

        return $path === false ? null : $path;
    }

    /**
     * Store a validated image upload on the public disk and downscale it to 1600px wide.
     */
    private function storeUploadedImage(?UploadedFile $file, string $directory = 'media'): ?string
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
