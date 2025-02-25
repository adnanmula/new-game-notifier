<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Infrastructure\Persistence\Repository\App;

use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\App;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\AppRepository;
use AdnanMula\Steam\NewGameNotifier\Infrastructure\Persistence\Repository\DbalRepository;

final class AppDbalRepository extends DbalRepository implements AppRepository
{
    private const string TABLE = 'app';

    public function app(int $appId): ?App
    {
        $result = $this->connection->createQueryBuilder()
            ->select('a.app_id, a.name, a.icon, a.playtime')
            ->from(self::TABLE, 'a')
            ->where('a.app_id = :appId')
            ->setParameter('appId', $appId)
            ->executeQuery()
            ->fetchAssociative();

        if (false === $result) {
            return null;
        }

        return $this->map($result);
    }

    /** @return array<int> */
    public function all(): array
    {
        $ids = $this->connection->createQueryBuilder()
            ->select('a.app_id')
            ->from(self::TABLE, 'a')
            ->executeQuery()
            ->fetchAllAssociative();

        return \array_map(static fn ($app) => $app['app_id'], $ids);
    }

    public function save(App $app): void
    {
        $stmt = $this->connection->prepare(
            \sprintf(
                '
                    INSERT INTO %s (app_id, name, icon, playtime)
                    VALUES (:app_id, :name, :icon, :playtime)
                    ON CONFLICT (app_id) DO UPDATE SET 
                    app_id = :app_id,
                    name = :name,
                    icon = :icon,
                    playtime = :playtime
                ',
                self::TABLE,
            ),
        );

        $stmt->bindValue('app_id', $app->appid, \PDO::PARAM_INT);
        $stmt->bindValue('name', $app->name, \PDO::PARAM_STR);
        $stmt->bindValue('icon', $app->icon, \PDO::PARAM_STR);
        $stmt->bindValue('playtime', $app->playedTime, \PDO::PARAM_INT);

        $stmt->executeStatement();
    }

    private function map(array $result): App
    {
        return new App($result['app_id'], $result['name'], $result['icon'], $result['playtime']);
    }
}
