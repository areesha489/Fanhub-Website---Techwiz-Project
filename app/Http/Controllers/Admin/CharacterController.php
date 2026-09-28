<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Character;
use App\Models\CharacterRelation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CharacterController extends Controller
{
    /**
     * Paginated character list with search, category filter and sorting.
     */
    public function index(Request $request): View
    {
        $query = Character::query()->with('category');

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->search($search);
        }

        $categoryId = (int) $request->query('category', 0);
        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        $featured = (string) $request->query('featured', '');
        if ($featured === '1') {
            $query->where('featured', true);
        } elseif ($featured === '0') {
            $query->where('featured', false);
        }

        $sortOptions = [
            'newest' => 'Newest first',
            'name' => 'Name A → Z',
            'popularity' => 'Most popular',
            'views' => 'Most viewed',
        ];

        $sort = (string) $request->query('sort', 'newest');
        if (! isset($sortOptions[$sort])) {
            $sort = 'newest';
        }

        match ($sort) {
            'name' => $query->orderBy('name', 'asc'),
            'popularity' => $query->orderByDesc('popularity'),
            'views' => $query->orderByDesc('views_count'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $items = $query->paginate(25)->withQueryString();

        return view('admin.characters.index', [
            'items' => $items,
            'search' => $search,
            'categoryId' => $categoryId,
            'featured' => $featured,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Blank form for a new character.
     */
    public function create(): View
    {
        return view('admin.characters.form', [
            'character' => new Character,
            'formTitle' => 'Add character',
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(['id', 'name']),
            'allCharacters' => Character::query()->orderBy('name')->get(['id', 'name']),
            'relations' => collect(),
            'relationTypes' => CharacterRelation::RELATIONS,
        ]);
    }

    /**
     * Persist a new character.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCharacter($request);

        $character = new Character;
        $character->fill($this->mapFields($request, $validated));
        $character->slug = $this->uniqueSlug($character->name);
        $character->save();

        return redirect()
            ->route('admin.characters.edit', $character)
            ->with('success', 'Character "'.$character->name.'" created.');
    }

    /**
     * Edit form with the related-characters editor.
     */
    public function edit(Character $character): View
    {
        return view('admin.characters.form', [
            'character' => $character,
            'formTitle' => 'Edit character',
            'categories' => Category::query()->orderBy('position')->orderBy('name')->get(['id', 'name']),
            'allCharacters' => Character::query()->where('id', '!=', $character->id)->orderBy('name')->get(['id', 'name']),
            'relations' => CharacterRelation::query()
                ->where('character_id', $character->id)
                ->with('relatedCharacter:id,name,slug,image')
                ->latest()
                ->get(),
            'relationTypes' => CharacterRelation::RELATIONS,
        ]);
    }

    /**
     * Update a character; also handles the relation add/delete side-forms.
     */
    public function update(Request $request, Character $character): RedirectResponse
    {
        $relationAction = (string) $request->input('relation_action', '');

        if ($relationAction === 'add') {
            return $this->addRelation($request, $character);
        }

        if ($relationAction === 'delete') {
            return $this->deleteRelation($request, $character);
        }

        $validated = $this->validateCharacter($request);
        $originalName = (string) $character->getOriginal('name');

        $character->fill($this->mapFields($request, $validated));

        if ($character->name !== $originalName) {
            $character->slug = $this->uniqueSlug($character->name, $character->id);
        }

        $character->save();

        return redirect()
            ->route('admin.characters.index')
            ->with('success', 'Character "'.$character->name.'" updated.');
    }

    /**
     * Delete a character (related rows cascade).
     */
    public function destroy(Character $character): RedirectResponse
    {
        $name = $character->name;

        if ($character->image) {
            Storage::disk('public')->delete($character->image);
        }

        foreach ((array) $character->gallery as $image) {
            if (is_string($image)) {
                Storage::disk('public')->delete($image);
            }
        }

        $character->delete();

        return redirect()
            ->route('admin.characters.index')
            ->with('success', 'Character "'.$name.'" deleted.');
    }

    /**
     * Attach a related character (relation_action=add side-form).
     */
    private function addRelation(Request $request, Character $character): RedirectResponse
    {
        $validated = $request->validate([
            'related_character_id' => ['required', 'integer', 'exists:characters,id', 'not_in:'.$character->id],
            'relation' => ['required', Rule::in(CharacterRelation::RELATIONS)],
        ]);

        $duplicate = CharacterRelation::query()
            ->where('character_id', $character->id)
            ->where('related_character_id', $validated['related_character_id'])
            ->exists();

        if ($duplicate) {
            return back()->with('error', 'These characters are already linked.');
        }

        CharacterRelation::create([
            'character_id' => $character->id,
            'related_character_id' => (int) $validated['related_character_id'],
            'relation' => $validated['relation'],
        ]);

        return back()->with('success', 'Relationship added.');
    }

    /**
     * Remove a relationship row (relation_action=delete side-form).
     */
    private function deleteRelation(Request $request, Character $character): RedirectResponse
    {
        $validated = $request->validate([
            'relation_id' => ['required', 'integer', 'exists:character_relations,id'],
        ]);

        CharacterRelation::query()
            ->whereKey($validated['relation_id'])
            ->where('character_id', $character->id)
            ->delete();

        return back()->with('success', 'Relationship removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCharacter(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'fandom' => ['nullable', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:4000'],
            'gender' => ['nullable', 'string', 'max:40'],
            'age' => ['nullable', 'string', 'max:60'],
            'affiliation' => ['nullable', 'string', 'max:150'],
            'abilities' => ['nullable', 'string', 'max:600'],
            'debut' => ['nullable', 'string', 'max:120'],
            'popularity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_gallery' => ['nullable', 'array'],
            'remove_gallery.*' => ['string'],
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
            'fandom' => $validated['fandom'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'age' => $validated['age'] ?? null,
            'affiliation' => $validated['affiliation'] ?? null,
            'abilities' => $validated['abilities'] ?? null,
            'debut' => $validated['debut'] ?? null,
            'popularity' => (int) ($validated['popularity'] ?? 0),
            'featured' => $request->boolean('featured'),
        ];

        if ($request->hasFile('image')) {
            $fields['image'] = $this->storeUploadedImage($request->file('image'), 'characters');
        }

        $gallery = array_values(array_filter((array) $this->currentGallery($request), 'is_string'));

        foreach ((array) $request->input('remove_gallery', []) as $remove) {
            $gallery = array_values(array_filter($gallery, fn (string $path) => $path !== $remove));
        }

        if ($request->hasFile('gallery')) {
            foreach ((array) $request->file('gallery') as $upload) {
                if ($upload instanceof UploadedFile && $upload->isValid()) {
                    $stored = $this->storeUploadedImage($upload, 'characters/gallery');
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
     * Gallery paths already attached before this request was mapped.
     *
     * @return array<int, string>
     */
    private function currentGallery(Request $request): array
    {
        $existing = $request->input('_current_gallery');

        if (is_array($existing)) {
            return array_values(array_filter($existing, 'is_string'));
        }

        return [];
    }

    /**
     * Slug derived from the name, made unique against the table.
     */
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'character-'.Str::lower(Str::random(6));

        $slug = $base;
        $suffix = 2;

        while (
            Character::query()
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
    private function storeUploadedImage(?UploadedFile $file, string $directory = 'characters'): ?string
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
