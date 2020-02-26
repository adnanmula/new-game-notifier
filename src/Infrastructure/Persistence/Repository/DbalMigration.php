<?php declare(strict_types=1);

namespace DemigrantSoft\Steam\NewGameNotifier\Infrastructure\Persistence\Repository;

use DemigrantSoft\Steam\NewGameNotifier\Domain\Service\Persistence\Migration;
use Doctrine\DBAL\Connection;

final class DbalMigration implements Migration
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function up(): void
    {
        $this->connection->exec('
          CREATE TABLE app (
                app_id int NOT NULL,
                name character varying(128) NOT NULL,
                icon character varying(128) NOT NULL,
                header character varying(128) NOT NULL,
                PRIMARY KEY(app_id)
            )'
        );
    }

    public function down(): void
    {
        $this->connection->exec('DROP TABLE IF EXISTS app');
    }
}
