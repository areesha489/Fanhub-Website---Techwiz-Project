<?php

namespace Database\Factories\Concerns;

/**
 * Original, non-lorem copy helpers shared by the Fan Hub+ factories.
 *
 * Titles are generated through a mixed-radix index so a batch of factories
 * always yields distinct slugs without relying on an exhaustible pool.
 */
trait GeneratesFandomCopy
{
    /**
     * @var list<string>
     */
    protected const FANHUB_ADJECTIVES = [
        'Silent', 'Crimson', 'Neon', 'Frozen', 'Hidden', 'Golden', 'Shattered', 'Wandering',
        'Electric', 'Midnight', 'Emerald', 'Iron', 'Stormbound', 'Radiant', 'Hollow', 'Blooming',
        'Sovereign', 'Restless', 'Violet', 'Ashen', 'Lunar', 'Cobalt', 'Umbral', 'Saffron',
        'Glacial', 'Molten', 'Velvet', 'Rusted', 'Solar', 'Astral', 'Copper', 'Feral',
        'Glass', 'Ivory', 'Jade', 'Obsidian', 'Wild', 'Kindled', 'Silver', 'Ancient',
    ];

    /**
     * @var list<string>
     */
    protected const FANHUB_NOUNS = [
        'Harbor', 'Vanguard', 'Chronicle', 'Parade', 'Symphony', 'Citadel', 'Frontier', 'Requiem',
        'Circuit', 'Lantern', 'Odyssey', 'Bastion', 'Mirage', 'Pulse', 'Garden', 'Summit',
        'Tide', 'Ember', 'Prism', 'Archive', 'Signal', 'Rebellion', 'Carnival', 'Atlas',
        'Beacon', 'Compass', 'Dunes', 'Echo', 'Festival', 'Guild', 'Horizon', 'Ignition',
        'Lattice', 'Meridian', 'Nexus', 'Orchard', 'Pinnacle', 'Quarry', 'Rhapsody', 'Tavern',
    ];

    /**
     * @var list<string>
     */
    protected const FANHUB_SUFFIXES = [
        '', '', ' Chronicles', ' Saga', ' Rising', ' Origins', ' Beyond', ' Protocol',
    ];

    /**
     * @var list<string>
     */
    protected const FANHUB_SENTENCES = [
        'A fan-favourite pick that keeps community rankings heated long after release.',
        'Weekly deep dives, character spotlights and community polls live in its hub.',
        'Known for fearless art direction and a soundtrack fans still stream on loop.',
        'The underdog story that turned a small studio into a household name.',
        'Cosplay groups keep returning to it for its instantly recognisable silhouettes.',
        'A slow-burn narrative that earns every twist with quiet, patient character work.',
        'Speedrunners, traders and lore hunters all claim it as their home turf.',
        'Every chapter ends on a cliffhanger the timeline never forgives.',
        'Its tournament circuit fills arenas and watch parties across three continents.',
        'Merch drops sell out in minutes and the resale market never cools down.',
        'Newcomers start here; veterans still quote it in every debate.',
        'The soundtrack alone trends every time a new season is announced.',
        'Fan artists have redrawn its opening frame hundreds of times over.',
        'Panel discussions about it routinely overrun their allotted slot.',
        'A comfort rewatch that somehow rewards the tenth viewing as much as the first.',
        'Community guides for it remain the most visited pages on the site.',
    ];

    /**
     * @var list<string>
     */
    protected const FANHUB_FANDOMS = [
        'Starfall Chronicles', 'Neon District', 'Moonlit Arena', 'Crimson Skies',
        'Verdant Spirits', 'Galactic Courier', 'Pixel Kingdom', 'Idol Stage',
        'Ronin Circuit', 'Deep Harbor', 'Aurora Tactics', 'Static Hearts',
        'Bloom Academy', 'Titan Forge', 'Wandering Stars', 'Lantern Republic',
    ];

    /**
     * @var list<string>
     */
    protected const FANHUB_GENRES = [
        'Action', 'Adventure', 'Comedy', 'Drama', 'Fantasy', 'Sci-Fi',
        'Romance', 'Thriller', 'Slice of Life', 'Sports', 'Mystery', 'Horror',
    ];

    /**
     * A two-word fandom-style title guaranteed to be unique per process.
     */
    protected function uniqueTitle(): string
    {
        $adjectiveCount = count(self::FANHUB_ADJECTIVES);
        $nounCount = count(self::FANHUB_NOUNS);
        $suffixCount = count(self::FANHUB_SUFFIXES);

        $index = fake()->unique()->numberBetween(0, ($adjectiveCount * $nounCount * $suffixCount) - 1);

        $adjective = self::FANHUB_ADJECTIVES[$index % $adjectiveCount];
        $noun = self::FANHUB_NOUNS[intdiv($index, $adjectiveCount) % $nounCount];
        $suffix = self::FANHUB_SUFFIXES[intdiv($index, $adjectiveCount * $nounCount) % $suffixCount];

        return trim($adjective.' '.$noun.$suffix);
    }

    /**
     * A compact two-sentence description.
     */
    protected function blurb(): string
    {
        return implode(' ', fake()->randomElements(self::FANHUB_SENTENCES, 2));
    }

    /**
     * A multi-paragraph body for articles and long-form copy.
     */
    protected function longCopy(int $paragraphs = 4): string
    {
        $blocks = [];

        for ($paragraph = 0; $paragraph < $paragraphs; $paragraph++) {
            $blocks[] = implode(' ', fake()->randomElements(self::FANHUB_SENTENCES, 3));
        }

        return implode("\n\n", $blocks);
    }

    /**
     * A fandom name used by characters, merchandise and profiles.
     */
    protected function fandom(): string
    {
        return fake()->randomElement(self::FANHUB_FANDOMS);
    }

    /**
     * A catalogue genre.
     */
    protected function genre(): string
    {
        return fake()->randomElement(self::FANHUB_GENRES);
    }
}
