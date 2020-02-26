<?php declare(strict_types=1);

namespace DemigrantSoft\Steam\NewGameNotifier\Infrastructure\Persistence\Repository;

use Doctrine\DBAL\Connection;

abstract class DbalRepository
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
}
