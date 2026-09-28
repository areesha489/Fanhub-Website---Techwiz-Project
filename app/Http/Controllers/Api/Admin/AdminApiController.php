<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Shared behaviour for the admin CRUD endpoints: search/status/category
 * filters, slug generation and image upload handling.
 */
abstract class AdminApiController extends ApiController
{
    /**
     * Apply a free-text `q` filter across the given columns.
     *
     * @param  list<string>  $columns
     */
    protected function applySearch(Request $request, Builder $query, array $columns, string $param = 'q'): Builder
    {
        $term = trim((string) $request->input($param));

        if ($term === '') {
            return $query;
        }

        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';

        return $query->where(function (Builder $inner) use ($columns, $like): void {
            foreach ($columns as $index => $column) {
                if ($index === 0) {
                    $inner->where($column, 'like', $like);
                } else {
                    $inner->orWhere($column, 'like', $like);
                }
            }
        });
    }

    /**
     * Apply the optional `status` filter.
     */
    protected function applyStatus(Request $request, Builder $query): Builder
    {
        $status = $request->input('status');

        if (is_string($status) && $status !== '') {
            $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Apply the optional `category` filter (slug or id).
     */
    protected function applyCategory(Request $request, Builder $query): Builder
    {
        if ($categoryId = $this->categoryId($request->input('category'))) {
            $query->where('category_id', $categoryId);
        }

        return $query;
    }

    /**
     * Resolve an image field: multipart upload, explicit clear, echoed URL
     * round-trip (converted back to a relative path) or the stored value.
     */
    protected function uploadImage(Request $request, string $field, ?string $existing = null, string $directory = 'uploads'): ?string
    {
        if ($request->hasFile($field)) {
            return 'storage/'.$request->file($field)->store($directory, 'public');
        }

        if (! $request->has($field)) {
            return $existing;
        }

        $value = $request->input($field);

        if (! is_string($value) || $value === '') {
            return null;
        }

        $base = rtrim(url('/'), '/');

        // A GET round-trip returns absolute URLs; map them back to storage.
        if (str_starts_with($value, $base.'/ph/')) {
            return null;
        }

        if (str_starts_with($value, $base.'/')) {
            return substr($value, strlen($base) + 1);
        }

        return $value;
    }

    /**
     * Build a slug that is unique inside the given table.
     */
    protected function uniqueSlug(string $table, string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);

        if ($slug === '') {
            $slug = 'item-'.Str::lower(Str::random(6));
        }

        $base = $slug;
        $suffix = 1;

        while (
            DB::table($table)
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
