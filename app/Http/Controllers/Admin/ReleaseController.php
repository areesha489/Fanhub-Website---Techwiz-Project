<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Content;
use App\Models\Release;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReleaseController extends Controller
{
    /**
     * Paginated release list with search, type/status filters and sorting.
     */
    public function index(Request $request): View
    {
        $query = Release::query()->with(['category', 'content']);

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->search($search);
        }

        $type = (string) $request->query('type', '');
        if (in_array($type, Release::TYPES, true)) {
            $query->where('release_type', $type);
        }

        $status = (string) $request->query('status', '');
        if (in_array($status, [Release::STATUS_UPCOMING, Release::STATUS_RELEASED, Release::STATUS_DELAYED], true)) {
            $query->where('status', $status);
        }

        $sortOptions = [
            'upcoming' => 'Soonest first',
            'newest' => 'Newest first',
            'title' => 'Title A → Z',
            'popularity' => 'Most popular',
        ];

        $sort = (string) $request->query('sort', 'upcoming');
        if (! isset($sortOptions[$sort])) {
            $sort = 'upcoming';
        }

        match ($sort) {
            'newest' => $query->orderBy('created_at', 'desc'),
            'title' => $query->orderBy('title', 'asc'),
            'popularity' => $query->orderByDesc('popularity'),
            default => $query->orderBy('release_date', 'asc'),
        };

        $items = $query->paginate(25)->withQueryString();

        return view('admin.releases.index', [
            'items' => $items,
            'search' => $search,
            'type' => $type,
            'status' => $status,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
            'releaseTypes' => Release::TYPES,
        ]);
    }

    /**
     * Blank form for a new release.
     */
    public function create(): View
    {
        return view('admin.releases.form', [
            'release' => new Release,
            'formTitle' => 'Add release',
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(['id', 'name']),
            'contents' => Content::query()->orderBy('title')->get(['id', 'title']),
            'releaseTypes' => Release::TYPES,
        ]);
    }

    /**
     * Persist a new release.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRelease($request);

        $release = new Release;
        $release->fill($this->mapFields($request, $validated));
        $release->slug = $this->uniqueSlug($release->title);
        $release->save();

        return redirect()
            ->route('admin.releases.edit', $release)
            ->with('success', 'Release "'.$release->title.'" created.');
    }

    /**
     * Edit form for an existing release.
     */
    public function edit(Release $release): View
    {
        return view('admin.releases.form', [
            'release' => $release,
            'formTitle' => 'Edit release',
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(['id', 'name']),
            'contents' => Content::query()->orderBy('title')->get(['id', 'title']),
            'releaseTypes' => Release::TYPES,
        ]);
    }

    /**
     * Update a release (slug follows a title change).
     */
    public function update(Request $request, Release $release): RedirectResponse
    {
        $validated = $this->validateRelease($request);
        $originalTitle = (string) $release->getOriginal('title');

        $release->fill($this->mapFields($request, $validated));

        if ($release->title !== $originalTitle) {
            $release->slug = $this->uniqueSlug($release->title, $release->id);
        }

        $release->save();

        return redirect()
            ->route('admin.releases.index')
            ->with('success', 'Release "'.$release->title.'" updated.');
    }

    /**
     * Delete a release and its poster.
     */
    public function destroy(Release $release): RedirectResponse
    {
        $title = $release->title;

        if ($release->poster) {
            Storage::disk('public')->delete($release->poster);
        }

        $release->delete();

        return redirect()
            ->route('admin.releases.index')
            ->with('success', 'Release "'.$title.'" deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRelease(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'release_type' => ['required', 'in:'.implode(',', Release::TYPES)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'release_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:3000'],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'in:'.implode(',', [Release::STATUS_UPCOMING, Release::STATUS_RELEASED, Release::STATUS_DELAYED])],
            'content_id' => ['nullable', 'exists:contents,id'],
            'popularity' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function mapFields(Request $request, array $validated): array
    {
        $fields = [
            'title' => $validated['title'],
            'release_type' => $validated['release_type'],
            'category_id' => $validated['category_id'] ?? null,
            'release_date' => $validated['release_date'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'content_id' => $validated['content_id'] ?? null,
            'popularity' => (int) ($validated['popularity'] ?? 0),
        ];

        if ($request->hasFile('poster')) {
            $fields['poster'] = $this->storeUploadedImage($request->file('poster'), 'releases');
        }

        return $fields;
    }

    /**
     * Slug derived from the title, made unique against the table.
     */
    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'release-'.Str::lower(Str::random(6));

        $slug = $base;
        $suffix = 2;

        while (
            Release::query()
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
    private function storeUploadedImage(?UploadedFile $file, string $directory = 'releases'): ?string
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
