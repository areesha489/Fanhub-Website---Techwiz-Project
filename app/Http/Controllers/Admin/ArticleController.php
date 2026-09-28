<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    /**
     * Paginated article list with search, status filter and sorting.
     */
    public function index(Request $request): View
    {
        $query = Article::query()->with(['category', 'author']);

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->search($search);
        }

        $status = (string) $request->query('status', '');
        if (in_array($status, [Article::STATUS_DRAFT, Article::STATUS_PUBLISHED, Article::STATUS_ARCHIVED], true)) {
            $query->where('status', $status);
        }

        $sortOptions = [
            'newest' => 'Newest first',
            'oldest' => 'Oldest first',
            'title' => 'Title A → Z',
            'views' => 'Most viewed',
        ];

        $sort = (string) $request->query('sort', 'newest');
        if (! isset($sortOptions[$sort])) {
            $sort = 'newest';
        }

        match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'title' => $query->orderBy('title', 'asc'),
            'views' => $query->orderByDesc('views_count'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $items = $query->paginate(25)->withQueryString();

        return view('admin.articles.index', [
            'items' => $items,
            'search' => $search,
            'status' => $status,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
        ]);
    }

    /**
     * Blank form for a new article (author defaults to the current admin).
     */
    public function create(Request $request): View
    {
        $article = new Article;
        $article->author_id = $request->user()?->id;
        $article->published_at = now();

        return view('admin.articles.form', [
            'article' => $article,
            'formTitle' => 'Write article',
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(['id', 'name']),
            'authors' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    /**
     * Persist a new article.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateArticle($request);

        $article = new Article;
        $article->fill($this->mapFields($request, $validated));
        $article->slug = $this->uniqueSlug($article->title);
        $article->save();

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('success', 'Article "'.$article->title.'" created.');
    }

    /**
     * Edit form for an existing article.
     */
    public function edit(Article $article): View
    {
        return view('admin.articles.form', [
            'article' => $article,
            'formTitle' => 'Edit article',
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(['id', 'name']),
            'authors' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    /**
     * Update an article (slug follows a title change).
     */
    public function update(Request $request, Article $article): RedirectResponse
    {
        $validated = $this->validateArticle($request);
        $originalTitle = (string) $article->getOriginal('title');

        $article->fill($this->mapFields($request, $validated));

        if ($article->title !== $originalTitle) {
            $article->slug = $this->uniqueSlug($article->title, $article->id);
        }

        $article->save();

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article "'.$article->title.'" updated.');
    }

    /**
     * Delete an article and its cover artwork.
     */
    public function destroy(Article $article): RedirectResponse
    {
        $title = $article->title;

        if ($article->cover_image) {
            Storage::disk('public')->delete($article->cover_image);
        }

        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article "'.$title.'" deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateArticle(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'category_id' => ['required', 'exists:categories,id'],
            'author_id' => ['required', 'exists:users,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:200000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'in:'.implode(',', [Article::STATUS_DRAFT, Article::STATUS_PUBLISHED, Article::STATUS_ARCHIVED])],
            'reading_time' => ['nullable', 'integer', 'min:1', 'max:240'],
            'featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function mapFields(Request $request, array $validated): array
    {
        $body = (string) $validated['body'];

        $readingTime = $validated['reading_time'] ?? null;
        if ($readingTime === null) {
            $wordCount = str_word_count(strip_tags($body));
            $readingTime = (int) max(1, (int) ceil($wordCount / 200));
        }

        $fields = [
            'title' => $validated['title'],
            'category_id' => (int) $validated['category_id'],
            'author_id' => (int) $validated['author_id'],
            'excerpt' => $validated['excerpt'] ?? null,
            'body' => $body,
            'status' => $validated['status'],
            'reading_time' => (int) $readingTime,
            'featured' => $request->boolean('featured'),
            'published_at' => $validated['published_at'] ?? now(),
        ];

        if ($request->hasFile('cover_image')) {
            $fields['cover_image'] = $this->storeUploadedImage($request->file('cover_image'), 'articles');
        }

        return $fields;
    }

    /**
     * Slug derived from the title, made unique against the table.
     */
    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'article-'.Str::lower(Str::random(6));

        $slug = $base;
        $suffix = 2;

        while (
            Article::query()
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
    private function storeUploadedImage(?UploadedFile $file, string $directory = 'articles'): ?string
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
