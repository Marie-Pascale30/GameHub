<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Jaquettes génératives : aucune image protégée n'est téléchargée ni liée.
 * Le titre sert de graine : dégradé, motif et accent sont déterministes,
 * le titre est rendu en HTML (typographie de la page), la plateforme en badge.
 * Aucun attribut style="" : compatible avec la Content-Security-Policy stricte.
 */
final class Cover
{
    private static int $counter = 0;

    public static function render(array $game, string $size = 'card'): string
    {
        $title = (string) $game['title'];
        $seed = crc32(mb_strtolower($title));
        $uid = 'cv' . (++self::$counter);

        $h1 = $seed % 360;
        $h2 = ($h1 + 35 + ($seed >> 8) % 120) % 360;
        $h3 = ($h1 + 160 + ($seed >> 12) % 60) % 360;
        $variant = ($seed >> 16) % 5;

        $c1 = "hsl($h1 80% 52%)";
        $c2 = "hsl($h2 78% 24%)";
        $c3 = "hsl($h3 95% 64%)";

        [$main, $sub] = self::splitTitle($title);
        $initial = mb_strtoupper(mb_substr(preg_replace('/^(the|le|la|les)\s+/i', '', $main) ?: $main, 0, 1));

        $svg = '<svg class="cover__art" viewBox="0 0 300 400" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">'
            . '<defs>'
            . '<linearGradient id="' . $uid . 'b" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="' . $c1 . '"/><stop offset="1" stop-color="' . $c2 . '"/></linearGradient>'
            . '<radialGradient id="' . $uid . 'g" cx=".85" cy=".1" r=".75"><stop offset="0" stop-color="' . $c3 . '" stop-opacity=".65"/><stop offset="1" stop-color="' . $c3 . '" stop-opacity="0"/></radialGradient>'
            . '<linearGradient id="' . $uid . 's" x1="0" y1="0" x2="0" y2="1"><stop offset=".35" stop-color="#05060f" stop-opacity="0"/><stop offset="1" stop-color="#05060f" stop-opacity=".92"/></linearGradient>'
            . '</defs>'
            . '<rect width="300" height="400" fill="url(#' . $uid . 'b)"/>'
            . '<rect width="300" height="400" fill="url(#' . $uid . 'g)"/>'
            . self::pattern($variant, $seed, $c2, $c3, $uid)
            . '<text x="292" y="300" text-anchor="end" class="cover__glyph">' . e($initial) . '</text>'
            . '<rect width="300" height="400" fill="url(#' . $uid . 's)"/>'
            . '</svg>';

        $platforms = $game['platforms'] ?? [];
        $badge = '';
        if ($platforms) {
            $extra = count($platforms) - 1;
            $badge = '<span class="cover__badge">' . e($platforms[0]['short_name'])
                . ($extra > 0 ? '<span class="cover__more">+' . $extra . '</span>' : '') . '</span>';
        }

        $longestWord = max(array_map('mb_strlen', explode(' ', $main)) ?: [1]);
        $len = mb_strlen($main);
        $scale = match (true) {
            $len <= 7 && $longestWord <= 7 => 'xl',
            $len <= 14 && $longestWord <= 9 => 'l',
            $len <= 24 && $longestWord <= 11 => 'm',
            default => 's',
        };

        return '<div class="cover cover--' . e($size) . ' cover--v' . $variant . '">'
            . $svg
            . $badge
            . '<span class="cover__text">'
            . '<span class="cover__title cover__title--' . $scale . '">' . e($main) . '</span>'
            . ($sub !== '' ? '<span class="cover__sub">' . e($sub) . '</span>' : '')
            . '<span class="cover__dev">' . e($game['developer']) . ' · ' . e($game['release_year']) . '</span>'
            . '</span>'
            . '</div>';
    }

    /** « The Witcher 3: Wild Hunt » → ["The Witcher 3", "Wild Hunt"]. */
    private static function splitTitle(string $title): array
    {
        $parts = preg_split('/\s*[:–—]\s+|\s+-\s+/u', $title, 2);
        return [trim($parts[0]), trim($parts[1] ?? '')];
    }

    private static function pattern(int $variant, int $seed, string $c2, string $c3, string $uid): string
    {
        $rand = self::prng($seed);
        $out = '';

        switch ($variant) {
            case 0: // Anneaux concentriques
                $cx = 150 + $rand() % 120;
                $cy = 60 + $rand() % 120;
                for ($r = 18; $r < 420; $r += 26) {
                    $out .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="none" stroke="#fff" stroke-opacity=".13" stroke-width="2"/>';
                }
                break;

            case 1: // Bandes diagonales
                $out .= '<g transform="rotate(' . (-20 - $rand() % 30) . ' 150 200)">';
                for ($x = -300; $x < 600; $x += 38) {
                    $w = 6 + $rand() % 14;
                    $out .= '<rect x="' . $x . '" y="-300" width="' . $w . '" height="1000" fill="#fff" fill-opacity=".08"/>';
                }
                $out .= '</g>';
                break;

            case 2: // Soleil rétro + grille en perspective
                $out .= '<circle cx="150" cy="190" r="86" fill="' . $c3 . '" fill-opacity=".85"/>';
                for ($i = 0; $i < 6; $i++) {
                    $y = 200 + $i * 12;
                    $out .= '<rect x="40" y="' . $y . '" width="220" height="' . (2 + $i) . '" fill="' . $c2 . '"/>';
                }
                $out .= '<rect x="0" y="262" width="300" height="140" fill="' . $c2 . '" fill-opacity=".75"/>';
                for ($x = -450; $x <= 750; $x += 75) {
                    $out .= '<line x1="150" y1="262" x2="' . $x . '" y2="400" stroke="' . $c3 . '" stroke-opacity=".45"/>';
                }
                foreach ([268, 280, 298, 324, 360] as $y) {
                    $out .= '<line x1="0" y1="' . $y . '" x2="300" y2="' . $y . '" stroke="' . $c3 . '" stroke-opacity=".4"/>';
                }
                break;

            case 3: // Éclats polygonaux
                for ($i = 0; $i < 9; $i++) {
                    $pts = [];
                    for ($k = 0; $k < 3; $k++) {
                        $pts[] = ($rand() % 360 - 30) . ',' . ($rand() % 420 - 10);
                    }
                    $opacity = number_format(0.05 + ($rand() % 12) / 100, 2, '.', '');
                    $out .= '<polygon points="' . implode(' ', $pts) . '" fill="#fff" fill-opacity="' . $opacity . '"/>';
                }
                break;

            default: // Trame de points + orbite
                $out .= '<defs><pattern id="' . $uid . 'p" width="16" height="16" patternUnits="userSpaceOnUse"><circle cx="8" cy="8" r="2" fill="#fff" fill-opacity=".2"/></pattern></defs>';
                $out .= '<rect width="300" height="400" fill="url(#' . $uid . 'p)"/>';
                $out .= '<circle cx="' . (60 + $rand() % 180) . '" cy="' . (80 + $rand() % 120) . '" r="' . (70 + $rand() % 50) . '" fill="none" stroke="' . $c3 . '" stroke-width="10" stroke-opacity=".55"/>';
                break;
        }

        return $out;
    }

    /** Petit générateur pseudo-aléatoire déterministe (LCG). */
    private static function prng(int $seed): \Closure
    {
        $state = $seed & 0x7fffffff;
        return static function () use (&$state): int {
            $state = (1103515245 * $state + 12345) & 0x7fffffff;
            return $state >> 8;
        };
    }
}
