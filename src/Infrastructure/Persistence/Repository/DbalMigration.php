<?php declare(strict_types=1);

namespace DemigrantSoft\Infrastructure\Persistence\Repository;

use DemigrantSoft\Domain\Persistence\Repository\Migration;
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
                type character varying(128) NOT NULL,
                name character varying(128) NOT NULL,
                header_image character varying(128) NOT NULL,
                PRIMARY KEY(app_id)
            )'
        );

        $this->connection->exec('
          CREATE TABLE owned_apps (
                user_id int NOT NULL,
                app_id int NOT NULL,
                PRIMARY KEY(user_id, app_id)
            )'
        );
    }

    public function down(): void
    {
        $this->connection->exec('DROP TABLE IF EXISTS app');
        $this->connection->exec('DROP TABLE IF EXISTS owned_apps');
    }
}
