<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Infrastructure\Persistence\Repository;

use Doctrine\DBAL\Connection;

abstract class DbalRepository
{
    public function __construct(protected Connection $connection)
    {}
}
