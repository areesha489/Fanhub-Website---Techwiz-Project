<?php

namespace App\Http\Controllers\Api;

use App\Models\Bookmark;
use App\Support\BookmarkHelper;
use App\Support\Engagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookmarkController extends ApiController
{
    /**
     * GET /api/bookmarks — the member's saved items (optional ?type= filter).
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->bookmarks()->latest('id');

        if ($type = $request->input('type')) {
            if (! BookmarkHelper::isValidType($type)) {
                throw ValidationException::withMessages([
                    'type' => ['Unknown bookmark type. Valid types: '.implode(', ', array_keys(BookmarkHelper::morphMap())).'.'],
                ]);
            }

            $query->where('bookmarkable_type', BookmarkHelper::morphMap()[$type]);
        }

        $paginator = $query->paginate($this->perPage($request))->withQueryString();

        return response()->json([
            'data' => $this->mapBookmarks($paginator->getCollection()),
            'meta' => $this->paginatorMeta($paginator),
        ]);
    }

    /**
     * POST /api/bookmarks — save an item (idempotent) with an optional note.
     *
     * Body: {type, id, note?} — type must be a valid bookmarkable slug (422).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string'],
            'id' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! BookmarkHelper::isValidType($data['type'])) {
            throw ValidationException::withMessages([
                'type' => ['Unknown bookmark type. Valid types: '.implode(', ', array_keys(BookmarkHelper::morphMap())).'.'],
            ]);
        }

        $modelClass = BookmarkHelper::morphMap()[$data['type']];
        $model = $modelClass::query()->findOrFail($data['id']);

        $bookmark = Bookmark::query()
            ->where('user_id', $request->user()->id)
            ->where('bookmarkable_type', $modelClass)
            ->where('bookmarkable_id', $model->getKey())
            ->first();

        if ($bookmark === null) {
            $result = BookmarkHelper::toggle($data['type'], $model->getKey(), $data['note'] ?? null);
            $bookmark = Bookmark::query()->find($result['id']);

            if ($bookmark !== null) {
                Engagement::log(
                    $request->user()->id,
                    'bookmark_added',
                    'Saved "'.($model->title ?? $model->name).'" to bookmarks.',
                    $modelClass,
                    $model->getKey()
                );
            }
        }

        if ($bookmark === null) {
            abort(500, 'The bookmark could not be created.');
        }

        $items = $this->mapBookmarks([$bookmark]);

        return response()->json(['data' => $items[0] ?? null], 201);
    }

    /**
     * PATCH /api/bookmarks/{bookmark} — update the personal note.
     */
    public function update(Request $request, Bookmark $bookmark): JsonResponse
    {
        if ($bookmark->user_id !== $request->user()->id) {
            abort(403, 'You may only edit your own bookmarks.');
        }

        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        BookmarkHelper::updateNote($bookmark, $data['note'] ?? null);
        $bookmark->refresh();

        $items = $this->mapBookmarks([$bookmark]);

        return response()->json(['data' => $items[0] ?? null]);
    }

    /**
     * DELETE /api/bookmarks/{bookmark} — remove a saved item.
     */
    public function destroy(Request $request, Bookmark $bookmark): JsonResponse
    {
        if ($bookmark->user_id !== $request->user()->id) {
            abort(403, 'You may only remove your own bookmarks.');
        }

        $modelClass = $bookmark->bookmarkable_type;
        $modelId = $bookmark->bookmarkable_id;
        $title = $bookmark->bookmarkable?->title ?? $bookmark->bookmarkable?->name ?? 'an item';

        $bookmark->delete();

        Engagement::log(
            $request->user()->id,
            'bookmark_removed',
            'Removed "'.$title.'" from bookmarks.',
            $modelClass,
            $modelId
        );

        return response()->json(['message' => 'Bookmark removed.']);
    }
}
