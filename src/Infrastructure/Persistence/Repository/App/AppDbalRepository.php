<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Infrastructure\Persistence\Repository\App;

use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\App;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\AppRepository;
use AdnanMula\Steam\NewGameNotifier\Infrastructure\Persistence\Repository\DbalRepository;

final class AppDbalRepository extends DbalRepository implements AppRepository
{
    private const TABLE = 'app';

    public function app(int $appId): ?App
    {
        $result = $this->connection->createQueryBuilder()
            ->select('a.app_id, a.name, a.icon, a.header')
            ->from(self::TABLE, 'a')
            ->where('a.app_id = :appId')
            ->setParameter('appId', $appId)
            ->execute()
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
            ->execute()
            ->fetchAllAssociative();

        return \array_map(static fn ($app) => $app['app_id'], $ids);
    }

    public function save(App $app): void
    {
        $stmt = $this->connection->prepare(
            \sprintf(
                '
                    INSERT INTO %s
                    (app_id, name, icon, header)
                    VALUES (:app_id, :name, :icon, :header)
                    ON CONFLICT (app_id) DO UPDATE SET 
                    app_id = :app_id,
                    name = :name,
                    icon = :icon,
                    header = :header
                ',
                self::TABLE,
            ),
        );

        $stmt->bindValue('app_id', $app->appid(), \PDO::PARAM_INT);
        $stmt->bindValue('name', $app->name(), \PDO::PARAM_STR);
        $stmt->bindValue('icon', $app->icon(), \PDO::PARAM_STR);
        $stmt->bindValue('header', $app->header(), \PDO::PARAM_STR);

        $stmt->execute();
    }

    private function map(array $result): App
    {
        return App::create($result['app_id'], $result['name'], $result['icon'], $result['header']);
    }
}
