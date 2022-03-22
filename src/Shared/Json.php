<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Shared;

final class Json
{
    public static function decode(string $json): array
    {
        return \json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
    }
}
