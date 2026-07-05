<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Domain\Model\App;

enum AppCompatibility: string
{
    case PLATINUM = 'platinum';
    case GOLD = 'gold';
    case SILVER = 'silver';
    case BRONZE = 'bronze';
    case BORKED = 'borked';
    case PENDING = 'pending';
    case UNKNOWN = 'unknown';
}
