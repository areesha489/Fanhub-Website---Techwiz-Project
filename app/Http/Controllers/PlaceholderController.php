<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Renders deterministic, cinematic SVG artwork for any label.
 *
 * GET /ph/{label}.svg?w=800&h=450&tone=anime
 *
 * The palette is derived from md5(label + tone), so the same label always
 * produces the same image (and it is cacheable for 7 days).
 */
class PlaceholderController extends Controller
{
    /**
     * Palette families: three background stops plus two accent colours.
     *
     * @var array<string, array{base: array<int, string>, a: string, b: string}>
     */
    private const PALETTES = [
        'violet' => ['base' => ['#06051a', '#140a32', '#291051'], 'a' => '#8B5CF6', 'b' => '#EC4899'],
        'magenta' => ['base' => ['#14061a', '#2b0a2a', '#4a0f3a'], 'a' => '#EC4899', 'b' => '#8B5CF6'],
        'cyan' => ['base' => ['#04121c', '#062634', '#0a3c4c'], 'a' => '#22D3EE', 'b' => '#3B82F6'],
        'blue' => ['base' => ['#050c22', '#0a1a44', '#102a63'], 'a' => '#3B82F6', 'b' => '#22D3EE'],
        'amber' => ['base' => ['#150d02', '#2e1a05', '#4a2a08'], 'a' => '#FBBF24', 'b' => '#FB7185'],
        'green' => ['base' => ['#04140f', '#062a1e', '#0a3d2c'], 'a' => '#34D399', 'b' => '#22D3EE'],
        'coral' => ['base' => ['#170610', '#31091c', '#4d1029'], 'a' => '#FB7185', 'b' => '#EC4899'],
    ];

    /**
     * Friendly tone names mapped onto palette families.
     *
     * @var array<string, string>
     */
    private const TONES = [
        'anime' => 'violet',
        'manga' => 'violet',
        'cosplay' => 'coral',
        'gaming' => 'cyan',
        'game' => 'cyan',
        'games' => 'cyan',
        'movie' => 'blue',
        'movies' => 'blue',
        'cinematic' => 'blue',
        'banner' => 'blue',
        'tv' => 'blue',
        'shows' => 'blue',
        'kpop' => 'magenta',
        'music' => 'magenta',
        'comic' => 'amber',
        'comics' => 'amber',
        'merch' => 'amber',
        'merchandise' => 'amber',
        'avatar' => 'violet',
        'profile' => 'violet',
        'poster' => 'violet',
        'character' => 'violet',
        'hero' => 'violet',
        'release' => 'cyan',
        'media' => 'cyan',
        'event' => 'coral',
        'article' => 'blue',
    ];

    /**
     * Build the placeholder SVG. Query supports w (50-2400), h (50-2400) and tone.
     */
    public function show(Request $request, string $label): Response
    {
        $label = trim($label) !== '' ? trim($label) : 'Fan Hub+';

        $width = max(50, min(2400, (int) $request->query('w', 800)));
        $height = max(50, min(2400, (int) $request->query('h', 450)));

        $toneValue = $request->query('tone');
        $tone = is_string($toneValue) && trim($toneValue) !== '' ? strtolower(trim($toneValue)) : null;

        $svg = $this->build($label, $width, $height, $tone);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'public,max-age=604800',
        ]);
    }

    /**
     * Compose the SVG document.
     */
    private function build(string $label, int $width, int $height, ?string $tone): string
    {
        $palette = $this->paletteFor($label, $tone);
        $seed = $this->seed(md5(mb_strtolower($label).'|'.($tone ?? '')));

        $w = $width;
        $h = $height;
        $min = min($w, $h);

        // Deterministic value in [0, 1).
        $r = fn (int $index): float => $seed[$index % 16] / 255;

        $safeLabel = e($label);
        $upperLabel = e(mb_strtoupper($label));
        $monogram = e($this->monogram($label));

        $monoSize = (int) round(min($w * 0.40, $h * 0.34));
        $monoSize = max(14, $monoSize);

        $showLabel = $w >= 220 && $h >= 140;
        $labelSize = 12;

        if ($showLabel) {
            $letters = max(1, mb_strlen($label));
            $labelSize = (int) floor(min($h * 0.085, ($w * 0.78) / ($letters * 0.68)));
            $labelSize = max(9, min($labelSize, (int) round($h * 0.16)));
        }

        $centerY = $h * ($h > $w ? 0.44 : 0.47);
        $monoBaseline = (int) round($centerY + $monoSize * 0.34);
        $barWidth = (int) round(min($w * 0.24, 260));
        $barHeight = max(3, (int) round($min * 0.012));
        $barY = (int) round($monoBaseline + $monoSize * 0.24);
        $labelBaseline = (int) round($barY + $barHeight + $labelSize * 1.55);

        $hexA = $palette['a'];
        $hexB = $palette['b'];

        $circleA = sprintf(
            '<circle cx="%s" cy="%s" r="%s" fill="url(#glowA)"/>',
            $this->n($w * (0.08 + 0.45 * $r(0))),
            $this->n($h * (0.02 + 0.42 * $r(1))),
            $this->n($min * (0.42 + 0.35 * $r(2))),
        );

        $circleB = sprintf(
            '<circle cx="%s" cy="%s" r="%s" fill="url(#glowB)"/>',
            $this->n($w * (0.55 + 0.45 * $r(3))),
            $this->n($h * (0.5 + 0.5 * $r(4))),
            $this->n($min * (0.35 + 0.4 * $r(5))),
        );

        $bandWidth = $w * (0.16 + 0.22 * $r(6));
        $bandHeight = $h * (0.75 + 0.55 * $r(7));
        $bandX = $w * (0.32 + 0.30 * $r(8));
        $bandY = -0.12 * $h;
        $skew = $w * (0.06 + 0.1 * $r(9));

        $band = sprintf(
            '<polygon points="%s" fill="url(#accent)" opacity="0.16" transform="rotate(%s %s %s)"/>',
            sprintf(
                '%s,%s %s,%s %s,%s %s,%s',
                $this->n($bandX),
                $this->n($bandY),
                $this->n($bandX + $bandWidth),
                $this->n($bandY),
                $this->n($bandX + $bandWidth - $skew),
                $this->n($bandY + $bandHeight),
                $this->n($bandX - $skew),
                $this->n($bandY + $bandHeight),
            ),
            $this->n(-14 - 18 * $r(10)),
            $this->n($bandX + $bandWidth / 2),
            $this->n($bandY + $bandHeight / 2),
        );

        $ring = sprintf(
            '<circle cx="%s" cy="%s" r="%s" fill="none" stroke="%s" stroke-width="%s" opacity="0.28"/>',
            $this->n($w * (0.68 + 0.3 * $r(11))),
            $this->n($h * (0.16 + 0.4 * $r(12))),
            $this->n($min * (0.2 + 0.22 * $r(13))),
            $hexA,
            $this->n(max(1.5, $min * 0.006)),
        );

        $dotGrid = $this->dotGrid($w, $h, $seed);

        $brandMark = '';
        if ($w >= 420 && $h >= 260) {
            $brandSize = max(10, (int) round(min($w, $h) * 0.035));
            $brandSize = min($brandSize, 20);
            $brandMark = sprintf(
                '<text x="%s" y="%s" text-anchor="start" font-family="Sora, Inter, sans-serif" font-size="%s" '
                .'font-weight="700" letter-spacing="4" fill="#FFFFFF" opacity="0.34">FAN HUB+</text>',
                $this->n($w * 0.045),
                $this->n($h - max(14, $h * 0.055)),
                $brandSize,
            );
        }

        $labelBlock = '';
        if ($showLabel) {
            $labelBlock = sprintf(
                '<rect x="%s" y="%s" width="%s" height="%s" rx="%s" fill="url(#accent)" opacity="0.9"/>'
                .'<text x="%s" y="%s" text-anchor="middle" font-family="Sora, Inter, sans-serif" font-size="%s" '
                .'font-weight="600" letter-spacing="%s" fill="#FFFFFF" opacity="0.82">%s</text>',
                $this->n($w / 2 - $barWidth / 2),
                $this->n($barY),
                $barWidth,
                $barHeight,
                $barHeight / 2,
                $this->n($w / 2),
                $this->n($labelBaseline),
                $labelSize,
                $this->n($labelSize * 0.18),
                $upperLabel,
            );
        }

        return implode('', [
            '<?xml version="1.0" encoding="UTF-8"?>',
            sprintf(
                '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d" '
                .'role="img" aria-label="Placeholder artwork for %s" preserveAspectRatio="xMidYMid slice">',
                $w,
                $h,
                $w,
                $h,
                $safeLabel,
            ),
            sprintf('<title>%s — Fan Hub+ placeholder artwork</title>', $safeLabel),
            '<defs>',
            sprintf(
                '<linearGradient id="base" x1="0" y1="0" x2="1" y2="1">'
                .'<stop offset="0" stop-color="%s"/><stop offset="0.55" stop-color="%s"/>'
                .'<stop offset="1" stop-color="%s"/></linearGradient>',
                $palette['base'][0],
                $palette['base'][1],
                $palette['base'][2],
            ),
            sprintf(
                '<radialGradient id="glowA" cx="0.5" cy="0.5" r="0.5">'
                .'<stop offset="0" stop-color="%s" stop-opacity="0.55"/>'
                .'<stop offset="1" stop-color="%s" stop-opacity="0"/></radialGradient>',
                $hexA,
                $hexA,
            ),
            sprintf(
                '<radialGradient id="glowB" cx="0.5" cy="0.5" r="0.5">'
                .'<stop offset="0" stop-color="%s" stop-opacity="0.45"/>'
                .'<stop offset="1" stop-color="%s" stop-opacity="0"/></radialGradient>',
                $hexB,
                $hexB,
            ),
            sprintf(
                '<linearGradient id="accent" x1="0" y1="0" x2="1" y2="1">'
                .'<stop offset="0" stop-color="%s"/><stop offset="1" stop-color="%s"/></linearGradient>',
                $hexA,
                $hexB,
            ),
            '<radialGradient id="vignette" cx="0.5" cy="0.45" r="0.75">'
            .'<stop offset="0.45" stop-color="#000000" stop-opacity="0"/>'
            .'<stop offset="1" stop-color="#000000" stop-opacity="0.55"/></radialGradient>',
            '<filter id="grain" x="0" y="0" width="100%" height="100%">'
            .'<feTurbulence type="fractalNoise" baseFrequency="0.85" numOctaves="4" stitchTiles="stitch" seed="7" result="noise"/>'
            .'<feColorMatrix in="noise" type="saturate" values="0"/></filter>',
            '</defs>',
            sprintf('<rect width="%d" height="%d" fill="url(#base)"/>', $w, $h),
            $circleA,
            $circleB,
            $band,
            $ring,
            $dotGrid,
            sprintf('<rect width="%d" height="%d" fill="url(#vignette)"/>', $w, $h),
            sprintf(
                '<rect width="%d" height="%d" filter="url(#grain)" opacity="0.10" style="mix-blend-mode:overlay"/>',
                $w,
                $h,
            ),
            sprintf(
                '<text x="%s" y="%s" text-anchor="middle" font-family="Sora, Inter, sans-serif" font-size="%d" '
                .'font-weight="800" letter-spacing="%s" fill="#FFFFFF" opacity="0.94">%s</text>',
                $this->n($w / 2),
                $this->n($monoBaseline),
                $monoSize,
                $this->n($monoSize * 0.04),
                $monogram,
            ),
            $labelBlock,
            $brandMark,
            '</svg>',
        ]);
    }

    /**
     * Pick a palette family for this label/tone combination.
     *
     * @return array{base: array<int, string>, a: string, b: string}
     */
    private function paletteFor(string $label, ?string $tone): array
    {
        $families = array_keys(self::PALETTES);

        if ($tone !== null) {
            if (isset(self::PALETTES[$tone])) {
                return self::PALETTES[$tone];
            }

            if (isset(self::TONES[$tone])) {
                return self::PALETTES[self::TONES[$tone]];
            }
        }

        $seed = md5($label.'|'.($tone ?? ''));
        $index = (int) hexdec(substr($seed, 0, 4)) % count($families);

        return self::PALETTES[$families[$index]];
    }

    /**
     * 16 deterministic bytes derived from an md5 hex string.
     *
     * @return array<int, int>
     */
    private function seed(string $hash): array
    {
        $bytes = [];

        for ($i = 0; $i < 16; $i++) {
            $bytes[] = (int) hexdec(substr($hash, $i * 2, 2));
        }

        return $bytes;
    }

    /**
     * Initials of up to two words (first letter of each).
     */
    private function monogram(string $label): string
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', trim($label)) ?: [];
        $words = array_values(array_filter($words, fn (string $word): bool => $word !== ''));

        if ($words === []) {
            return 'FH';
        }

        $letters = [];

        foreach (array_slice($words, 0, 2) as $word) {
            $letters[] = mb_strtoupper(mb_substr($word, 0, 1));
        }

        return implode('', $letters);
    }

    /**
     * Sparse decorative dot grid for wider artwork.
     */
    private function dotGrid(int $w, int $h, array $seed): string
    {
        if ($w < 320 || $h < 180) {
            return '';
        }

        $spacing = (int) round(max(28, min($w, $h) / 9));
        $radius = max(1, (int) round($spacing * 0.045));
        $rows = (int) floor($h / $spacing);
        $columns = (int) floor($w / $spacing);
        $shift = (int) ($seed[14] % $spacing);

        $dots = [];

        for ($row = 0; $row <= $rows; $row++) {
            for ($column = 0; $column <= $columns; $column++) {
                if ((($row * 7 + $column * 5 + $seed[15]) % 4) !== 0) {
                    continue;
                }

                $dots[] = sprintf(
                    '<circle cx="%d" cy="%d" r="%d"/>',
                    $column * $spacing + $shift / 2,
                    $row * $spacing + $shift / 2,
                    $radius,
                );
            }
        }

        if ($dots === []) {
            return '';
        }

        return '<g fill="#FFFFFF" opacity="0.10">'.implode('', $dots).'</g>';
    }

    /**
     * Format a float without trailing zeroes (compact SVG attributes).
     */
    private function n(float|int $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }
}
