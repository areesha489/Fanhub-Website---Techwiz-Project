<?php

if (! function_exists('ph')) {
    /**
     * URL of the deterministic placeholder artwork for a label.
     *
     * @param  string  $label  Text rendered into the generated SVG
     * @param  int  $w  Requested width in pixels
     * @param  int  $h  Requested height in pixels
     * @param  string|null  $tone  Optional colour tone hint
     */
    function ph(string $label, int $w = 800, int $h = 450, ?string $tone = null): string
    {
        return route('ph', [
            'label' => $label,
            'w' => $w,
            'h' => $h,
            'tone' => $tone,
        ]);
    }
}

if (! function_exists('img')) {
    /**
     * Use the stored image when one exists, otherwise the generated placeholder.
     *
     * @param  string|null  $url  Stored or external image URL
     * @param  string  $label  Text rendered into the fallback placeholder
     * @param  int  $w  Requested width in pixels
     * @param  int  $h  Requested height in pixels
     * @param  string|null  $tone  Optional colour tone hint
     */
    function img(?string $url, string $label, int $w = 800, int $h = 450, ?string $tone = null): string
    {
        if ($url !== null && $url !== '') {
            return $url;
        }

        return ph($label, $w, $h, $tone);
    }
}

if (! function_exists('catalogueStill')) {
    /**
     * A real catalogue still for a fandom, chosen stably from the title.
     */
    function catalogueStill(?string $tone, string $label): string
    {
        $pools = [
            'anime' => ['poster-frieren.png', 'attack-on-titan-poster.png', 'gojo-portrait.png', 'poster-anime-night.png', 'poster-anime-city.png', 'demon-slayer-banner.png'],
            'gaming' => ['elden-ring-poster.png', 'poster-hades.png', 'poster-game-roguelike.png', 'poster-game-party.png', 'poster-game-peak.png'],
            'movies' => ['miles-portrait.png', 'neon-chronicle-poster.png', 'poster-movie-desert.png', 'poster-movie-mind.png', 'poster-movie-multiverse.png', 'poster-movie-noir.png', 'poster-movie-orbit.png'],
            'tv-shows' => ['poster-tv.png', 'poster-tv-office.png', 'poster-tv-kitchen.png'],
            'k-pop' => ['poster-kpop.png', 'poster-kpop-stage.png'],
            'comics' => ['poster-comic.png', 'poster-comic-owl.png', 'merch-generic.png'],
            'manga' => ['one-piece-poster.png', 'poster-manga-alley.png', 'luffy-portrait.png'],
            'cosplay' => ['poster-cosplay.png', 'poster-craft.png', 'poster-cosplay-armor.png'],
        ];

        $files = $pools[$tone ?? ''] ?? $pools['anime'];
        $index = abs(crc32($label)) % count($files);

        return '/images/catalogue/'.$files[$index];
    }
}
