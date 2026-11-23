<?php

declare(strict_types=1);

use App\Core\Csrf;

/** Échappement HTML systématique de toute donnée affichée. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">';
}

function asset(string $path): string
{
    $file = BASE_PATH . '/public/assets/' . ltrim($path, '/');
    $version = is_file($file) ? substr((string) filemtime($file), -6) : '1';
    return '/assets/' . ltrim($path, '/') . '?v=' . $version;
}

function fr_date(?string $datetime, bool $withYear = true): string
{
    if (!$datetime) {
        return '';
    }
    $ts = strtotime($datetime);
    if ($ts === false) {
        return '';
    }
    $months = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    return (int) date('j', $ts) . ' ' . $months[(int) date('n', $ts) - 1] . ($withYear ? ' ' . date('Y', $ts) : '');
}

function fr_month_year(?string $datetime): string
{
    $ts = $datetime ? strtotime($datetime) : false;
    if ($ts === false) {
        return '';
    }
    $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    return $months[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
}

function time_ago(?string $datetime): string
{
    $ts = $datetime ? strtotime($datetime) : false;
    if ($ts === false) {
        return '';
    }
    $diff = time() - $ts;
    return match (true) {
        $diff < 60        => 'à l’instant',
        $diff < 3600      => 'il y a ' . intdiv($diff, 60) . ' min',
        $diff < 86400     => 'il y a ' . intdiv($diff, 3600) . ' h',
        $diff < 86400 * 2 => 'hier',
        $diff < 86400 * 30 => 'il y a ' . intdiv($diff, 86400) . ' jours',
        default           => 'le ' . fr_date($datetime),
    };
}

function plural(int $n, string $singular, ?string $plural = null): string
{
    return $n . ' ' . ($n > 1 ? ($plural ?? $singular . 's') : $singular);
}

function format_rating(float $avg): string
{
    return number_format($avg, 1, ',', '');
}

/** Initiales pour l'avatar (« lina_pixel » → « LP »). */
function initials(string $name): string
{
    $parts = preg_split('/[\s_.\-]+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [$name];
    $letters = mb_substr($parts[0], 0, 1) . (isset($parts[1]) ? mb_substr($parts[1], 0, 1) : mb_substr($parts[0], 1, 1));
    return mb_strtoupper($letters);
}

function avatar_tone(string $name): int
{
    return crc32(mb_strtolower($name)) % 8;
}

/** Étoiles en SVG (pas de style inline : compatible avec la CSP). */
function stars(float $rating, string $class = 'stars'): string
{
    $html = '<span class="' . e($class) . '" role="img" aria-label="' . e(format_rating($rating)) . ' sur 5">';
    for ($i = 1; $i <= 5; $i++) {
        $state = $rating >= $i - 0.25 ? 'full' : ($rating >= $i - 0.75 ? 'half' : 'empty');
        $html .= '<svg class="star star--' . $state . '" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.8l2.8 5.9 6.4.8-4.7 4.4 1.2 6.4L12 17.2l-5.7 3.1 1.2-6.4-4.7-4.4 6.4-.8z"/></svg>';
    }
    return $html . '</span>';
}

/** Icônes SVG inline (tracé maison, style « trait »). */
function icon(string $name, string $class = 'icon'): string
{
    static $paths = [
        'heart'    => 'M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z',
        'search'   => 'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM21 21l-4.35-4.35',
        'star'     => 'M12 2.8l2.8 5.9 6.4.8-4.7 4.4 1.2 6.4L12 17.2l-5.7 3.1 1.2-6.4-4.7-4.4 6.4-.8z',
        'sun'      => 'M12 17a5 5 0 1 0 0-10 5 5 0 0 0 0 10zM12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4',
        'moon'     => 'M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z',
        'user'     => 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z',
        'logout'   => 'M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9',
        'plus'     => 'M12 5v14M5 12h14',
        'edit'     => 'M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z',
        'trash'    => 'M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6',
        'shield'   => 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z',
        'gamepad'  => 'M6 11h4M8 9v4M15 12h.01M18 10h.01M17.3 5H6.7a4 4 0 0 0-4 3.6l-.9 7.2A2.7 2.7 0 0 0 4.5 19c.9 0 1.7-.4 2.2-1.1L8.5 15h7l1.8 2.9c.5.7 1.3 1.1 2.2 1.1a2.7 2.7 0 0 0 2.7-3.2l-.9-7.2a4 4 0 0 0-4-3.6z',
        'clock'    => 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM12 6v6l4 2',
        'trophy'   => 'M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0zM17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3',
        'calendar' => 'M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zM16 2v4M8 2v4M3 10h18',
        'code'     => 'M16 18l6-6-6-6M8 6l-6 6 6 6',
        'x'        => 'M18 6 6 18M6 6l12 12',
        'arrow'    => 'M19 12H5M12 19l-7-7 7-7',
        'key'      => 'M15.5 7.5 19 4l2 2-2 2 2 2-2 2-3.5-3.5M11.4 11.6a5.5 5.5 0 1 1-7.8 7.8 5.5 5.5 0 0 1 7.8-7.8zM11.4 11.6 15.5 7.5',
        'message'  => 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z',
        'layers'   => 'M12 2 2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5',
        'tag'      => 'M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8zM7 7h.01',
        'check'    => 'M20 6 9 17l-5-5',
        'sparkle'  => 'M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9zM19 17l.8 2.2L22 20l-2.2.8L19 23l-.8-2.2L16 20l2.2-.8z',
        'info'     => 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM12 16v-4M12 8h.01',
        'filter'   => 'M22 3H2l8 9.5V19l4 2v-8.5z',
        'mail'     => 'M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zM22 6l-10 7L2 6',
        'lock'     => 'M5 11h14a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2zM7 11V7a5 5 0 0 1 10 0v4',
    ];
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="' . ($paths[$name] ?? '') . '"/></svg>';
}
