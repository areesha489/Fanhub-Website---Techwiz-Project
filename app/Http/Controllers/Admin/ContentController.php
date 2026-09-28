<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Character;
use App\Models\Content;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    /**
     * Paginated catalogue with search, status/category filters and sorting.
     */
    public function index(Request $request): View
    {
        $query = Content::query()->with(['category', 'createdBy']);

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->search($search);
        }

        $status = (string) $request->query('status', '');
        if (in_array($status, [Content::STATUS_DRAFT, Content::STATUS_PUBLISHED, Content::STATUS_ARCHIVED], true)) {
            $query->where('status', $status);
        }

        $categoryId = (int) $request->query('category', 0);
        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        $type = (string) $request->query('type', '');
        if (in_array($type, Content::TYPES, true)) {
            $query->where('content_type', $type);
        }

        $sortOptions = [
            'newest' => 'Newest first',
            'oldest' => 'Oldest first',
            'title' => 'Title A → Z',
            'views' => 'Most viewed',
            'popularity' => 'Most popular',
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
            'popularity' => $query->orderByDesc('popularity'),
            'rating' => $query->orderByDesc('rating'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $items = $query->paginate(25)->withQueryString();

        return view('admin.content.index', [
            'items' => $items,
            'search' => $search,
            'status' => $status,
            'type' => $type,
            'categoryId' => $categoryId,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Blank form for a new title.
     */
    public function create(): View
    {
        return view('admin.content.form', [
            'content' => new Content,
            'formTitle' => 'Add content',
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(['id', 'name']),
            'characters' => Character::query()->orderBy('name')->get(['id', 'name']),
            'tags' => Tag::query()->orderBy('name')->get(['id', 'name']),
            'attachCharacters' => collect(),
            'attachTags' => collect(),
        ]);
    }

    /**
     * Persist a new content item with its tags and characters.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateContent($request);

        $content = new Content;
        $content->fill($this->mapFields($request, $validated));
        $content->slug = $this->uniqueSlug($content->title);
        $content->created_by = $request->user()?->id;
        $content->save();

        $this->syncTags($content, $request);
        $this->syncCharacters($content, $request);

        return redirect()
            ->route('admin.content.edit', $content)
            ->with('success', 'Content "'.$content->title.'" created.');
    }

    /**
     * Edit form for an existing title.
     */
    public function edit(Content $content): View
    {
        return view('admin.content.form', [
            'content' => $content,
            'formTitle' => 'Edit content',
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(['id', 'name']),
            'characters' => Character::query()->orderBy('name')->get(['id', 'name']),
            'tags' => Tag::query()->orderBy('name')->get(['id', 'name']),
            'attachCharacters' => $content->characters()->get(),
            'attachTags' => $content->tags()->get(),
        ]);
    }

    /**
     * Update a content item, its gallery links, tags and characters.
     */
    public function update(Request $request, Content $content): RedirectResponse
    {
        $validated = $this->validateContent($request, $content);
        $originalTitle = (string) $content->getOriginal('title');

        $content->fill($this->mapFields($request, $validated));

        if ($content->title !== $originalTitle) {
            $content->slug = $this->uniqueSlug($content->title, $content->id);
        }

        $content->save();

        $this->syncTags($content, $request);
        $this->syncCharacters($content, $request);

        return redirect()
            ->route('admin.content.index')
            ->with('success', 'Content "'.$content->title.'" updated.');
    }

    /**
     * Delete a content item and its uploaded artwork.
     */
    public function destroy(Content $content): RedirectResponse
    {
        $title = $content->title;

        foreach ([$content->cover_image, $content->banner_image] as $image) {
            if ($image) {
                Storage::disk('public')->delete($image);
            }
        }

        $content->delete();

        return redirect()
            ->route('admin.content.index')
            ->with('success', 'Content "'.$title.'" deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateContent(Request $request, ?Content $content = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'category_id' => ['required', 'exists:categories,id'],
            'content_type' => ['required', 'string', 'in:'.implode(',', Content::TYPES)],
            'genre' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'synopsis' => ['nullable', 'string', 'max:6000'],
            'release_date' => ['nullable', 'date'],
            'status' => ['required', 'in:'.implode(',', [Content::STATUS_DRAFT, Content::STATUS_PUBLISHED, Content::STATUS_ARCHIVED])],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'popularity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'featured' => ['nullable', 'boolean'],
            'trailer_url' => ['nullable', 'url', 'max:500'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'new_tags' => ['nullable', 'string', 'max:300'],
            'characters' => ['nullable', 'array'],
            'characters.*' => ['integer', 'exists:characters,id'],
            'character_roles' => ['nullable', 'array'],
            'character_roles.*' => ['nullable', 'string', 'max:120'],
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
            'category_id' => (int) $validated['category_id'],
            'content_type' => $validated['content_type'],
            'genre' => $validated['genre'] ?? null,
            'description' => $validated['description'] ?? null,
            'synopsis' => $validated['synopsis'] ?? null,
            'release_date' => $validated['release_date'] ?? null,
            'status' => $validated['status'],
            'rating' => $validated['rating'] ?? 0,
            'popularity' => (int) ($validated['popularity'] ?? 0),
            'featured' => $request->boolean('featured'),
            'trailer_url' => $validated['trailer_url'] ?? null,
        ];

        if ($request->hasFile('cover_image')) {
            $fields['cover_image'] = $this->storeUploadedImage($request->file('cover_image'), 'content');
        }

        if ($request->hasFile('banner_image')) {
            $fields['banner_image'] = $this->storeUploadedImage($request->file('banner_image'), 'content');
        }

        return $fields;
    }

    /**
     * Attach the picked tags, creating any comma-separated additions.
     */
    private function syncTags(Content $content, Request $request): void
    {
        $ids = array_values(array_filter((array) $request->input('tags', []), 'is_numeric'));

        $newNames = collect(explode(',', (string) $request->input('new_tags', '')))
            ->map(fn (string $name) => trim($name))
            ->filter(fn (string $name) => $name !== '')
            ->values();

        foreach ($newNames as $name) {
            $slug = Str::slug($name);
            if ($slug === '') {
                continue;
            }

            $tag = Tag::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => Str::limit($name, 60, ''), 'slug' => $slug],
            );

            $ids[] = $tag->id;
        }

        $content->tags()->sync(array_unique(array_map('intval', $ids)));
    }

    /**
     * Sync the content ↔ character pivot, keeping the optional role text.
     */
    private function syncCharacters(Content $content, Request $request): void
    {
        $ids = array_values(array_filter((array) $request->input('characters', []), 'is_numeric'));
        $roles = (array) $request->input('character_roles', []);

        $pivot = [];
        foreach ($ids as $id) {
            $pivot[(int) $id] = [
                'role' => trim((string) ($roles[(string) $id] ?? $roles[(int) $id] ?? '')),
            ];
        }

        $content->characters()->sync($pivot);
    }

    /**
     * Slug derived from the title, made unique against the table.
     */
    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'content-'.Str::lower(Str::random(6));

        $slug = $base;
        $suffix = 2;

        while (
            Content::query()
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
    private function storeUploadedImage(?UploadedFile $file, string $directory = 'content'): ?string
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
