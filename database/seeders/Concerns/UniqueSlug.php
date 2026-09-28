<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Guards against slug collisions when curated rows and factory rows share a
 * table (factories only guarantee uniqueness inside a single process).
 */
trait UniqueSlug
{
    /**
     * Slug for a title, suffixed until it is free inside the given table.
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
