<?php
namespace App\Common;

use DateTime;

class DateUtil
{
    public static function parseDate(string $format, ?string $input, ?bool $nullable = false): ?DateTime
    {
        if (is_null($input)) {
            return $nullable ? null : new DateTime();
        }
        return DateTime::createFromFormat($format, $input);
    }
}