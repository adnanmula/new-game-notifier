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
                appid UUID NOT NULL,
                username character varying(128) NOT NULL,
                password character varying(128) NOT NULL,
                PRIMARY KEY(id)
            )'
        );

        $this->connection->exec('ALTER TABLE users ADD CONSTRAINT "users_unique_username" UNIQUE ("username")');

        $this->connection->exec('
          CREATE TABLE scenario_invitations (
                user_id uuid NOT NULL,
                scenario_id uuid NOT NULL,
                status character varying(128) NOT NULL,
                date timestamp without time zone NOT NULL,
                date_joined timestamp without time zone,
                PRIMARY KEY(user_id, scenario_id)
            )'
        );
    }

    public function down(): void
    {
        $this->connection->exec('DROP TABLE IF EXISTS users');
        $this->connection->exec('DROP TABLE IF EXISTS scenario_invitations');
    }
}
