<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Paginated category list with search and sorting.
     */
    public function index(Request $request): View
    {
        $query = Category::query()->withCount('contents');

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->where(fn ($builder) => $builder->where('name', 'like', $like)
                ->orWhere('description', 'like', $like));
        }

        $sortOptions = [
            'position' => 'Menu position',
            'name' => 'Name A → Z',
            'contents' => 'Most content',
            'newest' => 'Newest first',
        ];

        $sort = (string) $request->query('sort', 'position');
        if (! isset($sortOptions[$sort])) {
            $sort = 'position';
        }

        match ($sort) {
            'name' => $query->orderBy('name', 'asc'),
            'contents' => $query->orderByDesc('contents_count'),
            'newest' => $query->orderByDesc('created_at'),
            default => $query->orderBy('position')->orderBy('name'),
        };

        $items = $query->paginate(25)->withQueryString();

        return view('admin.categories.index', [
            'items' => $items,
            'search' => $search,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
        ]);
    }

    /**
     * Blank form for a new category.
     */
    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new Category,
            'formTitle' => 'Add category',
        ]);
    }

    /**
     * Persist a new category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCategory($request);
        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $validated['icon'] = $this->normalizeIcon($validated['icon'] ?? null);
        $validated['image'] = $this->storeUploadedImage($request->file('image'));

        $category = Category::create($validated);

        return redirect()
            ->route('admin.categories.edit', $category)
            ->with('success', 'Category "'.$category->name.'" created.');
    }

    /**
     * Edit form for an existing category.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.form', [
            'category' => $category,
            'formTitle' => 'Edit category',
        ]);
    }

    /**
     * Update a category (slug is only regenerated when the name changes).
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $this->validateCategory($request, $category);
        $originalName = (string) $category->getOriginal('name');

        if ($validated['name'] !== $originalName) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], $category->id);
        }

        $validated['icon'] = $this->normalizeIcon($validated['icon'] ?? null);

        if ($request->hasFile('image')) {
            $old = $category->image;
            $validated['image'] = $this->storeUploadedImage($request->file('image'));

            if ($old) {
                Storage::disk('public')->delete($old);
            }
        }

        $category->fill($validated)->save();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category "'.$category->name.'" updated.');
    }

    /**
     * Delete a category (its content rows are removed by the FK cascade).
     */
    public function destroy(Category $category): RedirectResponse
    {
        $name = $category->name;

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category "'.$name.'" deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCategory(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:60', 'regex:/^(bi-?)?[a-z0-9][a-z0-9-]*$/'],
            'description' => ['nullable', 'string', 'max:2000'],
            'accent' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'position' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    /**
     * Normalise a Bootstrap icon to the canonical `bi-*` form (same rule the
     * public category card uses), or null when nothing was entered.
     */
    private function normalizeIcon(?string $icon): ?string
    {
        $icon = Str::lower(trim((string) $icon));

        if ($icon === '') {
            return null;
        }

        $icon = preg_replace('/[^a-z0-9-]/', '', $icon) ?? '';

        if ($icon === '') {
            return null;
        }

        return str_starts_with($icon, 'bi-') ? $icon : 'bi-'.$icon;
    }

    /**
     * Slug derived from the name, made unique against the table.
     */
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'category-'.Str::lower(Str::random(6));

        $slug = $base;
        $suffix = 2;

        while (
            Category::query()
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
    private function storeUploadedImage(?UploadedFile $file, string $directory = 'categories'): ?string
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
