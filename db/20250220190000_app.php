<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class App extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(
            'CREATE TABLE app (
                app_id int NOT NULL,
                name character varying(128) NOT NULL,
                icon character varying(128) NOT NULL,
                playtime int NOT NULL,
                PRIMARY KEY(app_id)
            )',
        );
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS "app"');
    }
}
