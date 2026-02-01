<?php
namespace App\Common;

class IdUtil
{
    public static function implode(?array $ids, string $separator = ', '): ?string
    {
        if (is_null($ids)) {
            return '';
        }
        return implode($separator, array_map(fn($item) => (int) $item, $ids));
    }
}