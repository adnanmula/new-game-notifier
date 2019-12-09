<?php declare(strict_types=1);

namespace DemigrantSoft\Infrastructure\Persistence\Repository\App;

use DemigrantSoft\Domain\App\AppRepository;
use DemigrantSoft\Domain\App\Model\App;
use DemigrantSoft\Infrastructure\Persistence\Repository\DbalRepository;

final class AppDbalRepository extends DbalRepository implements AppRepository
{
    private const APP_TABLE = 'app';
    private const OWNED_APPS_TABLE = 'owned_apps';

    public function app(int $appId): ?App
    {
        $result = $this->connection->createQueryBuilder()
            ->select('a.app_id, a.type, a.name, a.header_image')
            ->from(self::APP_TABLE, 'a')
            ->where('a.app_id = :appId')
            ->setParameter('appId', $appId)
            ->execute()
            ->fetch();

        if (false === $result) {
            return null;
        }

        return $this->map($result);
    }

    public function missing(array $appIds): array
    {
        $savedApps = $this->connection->createQueryBuilder()
            ->select('a.app_id')
            ->from(self::APP_TABLE, 'a')
            ->execute()
            ->fetchAll();

        if (false === $savedApps) {
            return [];
        }

        return \array_diff(
            $appIds,
            \array_map(fn($app) => $app['app_id'], $savedApps)
        );
    }

    public function save(App $app): void
    {
        $stmt = $this->connection->prepare('INSERT into app (app_id, type, name, header_image) values (:app_id, :type, :name, :header_image)');

        $stmt->bindValue('app_id', $app->appid());
        $stmt->bindValue('type', $app->type());
        $stmt->bindValue('name', $app->name());
        $stmt->bindValue('header_image', $app->header());

        $stmt->execute();
    }

    private function map($result)
    {
        return App::create(
            $result['app_id'],
            $result['type'],
            $result['name'],
            $result['header_image']
        );
    }
}
