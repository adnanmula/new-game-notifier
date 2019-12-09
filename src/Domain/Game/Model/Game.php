<?php declare(strict_types=1);

namespace DemigrantSoft\Domain\Game\Model;

final class Game
{
    private function __construct()
    {
    }

    public static function create()
    {
        return new self();
    }
}
