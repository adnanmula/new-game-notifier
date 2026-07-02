<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Shared;

final class Json
{
    public static function decode(string $json): array
    {
        $value = \json_decode($json, true, 512, \JSON_THROW_ON_ERROR);

        if (false === \is_array($value)) {
            throw new \RuntimeException('Invalid JSON data');
        }

        return $value;
    }
}
