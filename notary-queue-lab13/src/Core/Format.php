<?php
declare(strict_types=1);

/** Функсияҳои хурди намоиш: рақами талон, тарҷумаи ҳолат. */
final class Format
{
    public const STATUSES = [
        'waiting' => 'Интизорӣ', 'called' => 'Даъватшуда', 'in_service' => 'Дар хизмат',
        'done' => 'Анҷомёфта', 'no_show' => 'Наомада', 'cancelled' => 'Бекоршуда',
    ];
    public const TYPES = ['booked' => 'Сабти пешакӣ', 'live' => 'Бе сабт'];

    public static function label(string $type, int|string $no): string
    {
        return ($type === 'booked' ? 'B' : 'L') . '-' . str_pad((string) $no, 3, '0', STR_PAD_LEFT);
    }

    public static function status(string $status): string { return self::STATUSES[$status] ?? $status; }
    public static function type(string $type): string     { return self::TYPES[$type] ?? $type; }

    /** 'YYYY-MM-DD HH:MM:SS' -> 'HH:MM' */
    public static function time(?string $dt): string      { return $dt ? substr($dt, 11, 5) : '—'; }

    /** 'YYYY-MM-DD' -> 'DD.MM.YYYY' */
    public static function date(?string $d): string
    {
        if (!$d) {
            return '—';
        }
        [$y, $m, $day] = explode('-', substr($d, 0, 10));
        return "{$day}.{$m}.{$y}";
    }
}
