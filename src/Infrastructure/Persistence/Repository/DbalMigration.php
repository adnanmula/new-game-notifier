<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Infrastructure\Persistence\Repository;

use AdnanMula\Steam\NewGameNotifier\Domain\Service\Persistence\Migration;
use Doctrine\DBAL\Connection;

final class DbalMigration implements Migration
{
    public function __construct(protected Connection $connection)
    {}

    public function up(): void
    {
        $this->connection->executeStatement(
            'CREATE TABLE app (
                app_id int NOT NULL,
                name character varying(128) NOT NULL,
                icon character varying(128) NOT NULL,
                header character varying(128) NOT NULL,
                PRIMARY KEY(app_id)
            )',
        );
    }

    public function down(): void
    {
        $this->connection->executeStatement('DROP TABLE IF EXISTS app');
    }
}
