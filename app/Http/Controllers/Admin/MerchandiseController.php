<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Merchandise;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MerchandiseController extends Controller
{
    /**
     * Paginated merchandise list with search, status filter and sorting.
     */
    public function index(Request $request): View
    {
        $query = Merchandise::query()->with('category');

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->search($search);
        }

        $status = (string) $request->query('status', '');
        if (in_array($status, [Merchandise::STATUS_DRAFT, Merchandise::STATUS_PUBLISHED, Merchandise::STATUS_ARCHIVED], true)) {
            $query->where('status', $status);
        }

        $upcoming = (string) $request->query('upcoming', '');
        if ($upcoming === '1') {
            $query->where('is_upcoming', true);
        } elseif ($upcoming === '0') {
            $query->where('is_upcoming', false);
        }

        $sortOptions = [
            'newest' => 'Newest first',
            'title' => 'Title A → Z',
            'popularity' => 'Most popular',
            'price' => 'Lowest price',
        ];

        $sort = (string) $request->query('sort', 'newest');
        if (! isset($sortOptions[$sort])) {
            $sort = 'newest';
        }

        match ($sort) {
            'title' => $query->orderBy('title', 'asc'),
            'popularity' => $query->orderByDesc('popularity'),
            'price' => $query->orderBy('price'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $items = $query->paginate(25)->withQueryString();

        return view('admin.merchandise.index', [
            'items' => $items,
            'search' => $search,
            'status' => $status,
            'upcoming' => $upcoming,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
        ]);
    }

    /**
     * Blank form for a new merchandise item.
     */
    public function create(): View
    {
        return view('admin.merchandise.form', [
            'item' => new Merchandise,
            'formTitle' => 'Add merchandise',
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Persist a new merchandise item.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateItem($request);

        $item = new Merchandise;
        $item->fill($this->mapFields($request, $validated));
        $item->slug = $this->uniqueSlug($item->title);
        $item->save();

        return redirect()
            ->route('admin.merchandise.edit', $item)
            ->with('success', 'Merchandise "'.$item->title.'" created.');
    }

    /**
     * Edit form for an existing merchandise item.
     */
    public function edit(Merchandise $merchandise): View
    {
        return view('admin.merchandise.form', [
            'item' => $merchandise,
            'formTitle' => 'Edit merchandise',
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update a merchandise item (slug follows a title change).
     */
    public function update(Request $request, Merchandise $merchandise): RedirectResponse
    {
        $validated = $this->validateItem($request);
        $originalTitle = (string) $merchandise->getOriginal('title');

        $merchandise->fill($this->mapFields($request, $validated));

        if ($merchandise->title !== $originalTitle) {
            $merchandise->slug = $this->uniqueSlug($merchandise->title, $merchandise->id);
        }

        $merchandise->save();

        return redirect()
            ->route('admin.merchandise.index')
            ->with('success', 'Merchandise "'.$merchandise->title.'" updated.');
    }

    /**
     * Delete a merchandise item and its artwork.
     */
    public function destroy(Merchandise $merchandise): RedirectResponse
    {
        $title = $merchandise->title;

        if ($merchandise->image) {
            Storage::disk('public')->delete($merchandise->image);
        }

        $merchandise->delete();

        return redirect()
            ->route('admin.merchandise.index')
            ->with('success', 'Merchandise "'.$title.'" deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateItem(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'category_id' => ['required', 'exists:categories,id'],
            'fandom' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'currency' => ['nullable', 'string', 'size:3'],
            'tags' => ['nullable', 'string', 'max:300'],
            'is_upcoming' => ['nullable', 'boolean'],
            'release_date' => ['nullable', 'date'],
            'popularity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'status' => ['required', 'in:'.implode(',', [Merchandise::STATUS_DRAFT, Merchandise::STATUS_PUBLISHED, Merchandise::STATUS_ARCHIVED])],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function mapFields(Request $request, array $validated): array
    {
        $tags = array_values(array_filter(array_map(
            fn (string $tag) => trim($tag),
            explode(',', (string) ($validated['tags'] ?? '')),
        ), fn (string $tag) => $tag !== ''));

        $fields = [
            'title' => $validated['title'],
            'category_id' => (int) $validated['category_id'],
            'fandom' => $validated['fandom'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? null,
            'currency' => strtoupper((string) ($validated['currency'] ?? 'USD')),
            'tags' => $tags,
            'is_upcoming' => $request->boolean('is_upcoming'),
            'release_date' => $validated['release_date'] ?? null,
            'popularity' => (int) ($validated['popularity'] ?? 0),
            'status' => $validated['status'],
        ];

        if ($request->hasFile('image')) {
            $fields['image'] = $this->storeUploadedImage($request->file('image'), 'merchandise');
        }

        return $fields;
    }

    /**
     * Slug derived from the title, made unique against the table.
     */
    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'merchandise-'.Str::lower(Str::random(6));

        $slug = $base;
        $suffix = 2;

        while (
            Merchandise::query()
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
    private function storeUploadedImage(?UploadedFile $file, string $directory = 'merchandise'): ?string
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
