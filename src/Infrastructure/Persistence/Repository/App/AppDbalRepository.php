<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Infrastructure\Persistence\Repository\App;

use AdnanMula\Criteria\Criteria;
use AdnanMula\Criteria\DbalCriteriaAdapter;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\App;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\AppRepository;
use AdnanMula\Steam\NewGameNotifier\Infrastructure\Persistence\Repository\DbalRepository;

final class AppDbalRepository extends DbalRepository implements AppRepository
{
    private const string TABLE = 'app';

    public function app(int $appId): ?App
    {
        $result = $this->connection->createQueryBuilder()
            ->select('a.app_id, a.name, a.icon, a.playtime, a.review_score, a.review_amount')
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

    /** @return array<App> */
    public function search(Criteria $criteria): array
    {
        $builder = $this->connection->createQueryBuilder();

        $query = $builder->select('a.*')
            ->from(self::TABLE, 'a');

        new DbalCriteriaAdapter($builder)->execute($criteria);

        return \array_map(
            fn (array $row) => $this->map($row),
            $query->executeQuery()->fetchAllAssociative(),
        );
    }

    public function searchOne(Criteria $criteria): ?App
    {
        $result = $this->search(
            new Criteria($criteria->offset(), 1, $criteria->sorting(), ...$criteria->filterGroups())
        );

        return $result[0] ?? null;
    }

    public function save(App $app): void
    {
        $stmt = $this->connection->prepare(
            \sprintf(
                '
                    INSERT INTO %s (app_id, name, icon, playtime, review_score, review_amount)
                    VALUES (:app_id, :name, :icon, :playtime, :review_score, :review_amount)
                    ON CONFLICT (app_id) DO UPDATE SET 
                    app_id = :app_id,
                    name = :name,
                    icon = :icon,
                    playtime = :playtime,
                    review_score = :review_score,
                    review_amount = :review_amount
                ',
                self::TABLE,
            ),
        );

        $stmt->bindValue('app_id', $app->appid, \PDO::PARAM_INT);
        $stmt->bindValue('name', $app->name, \PDO::PARAM_STR);
        $stmt->bindValue('icon', $app->icon, \PDO::PARAM_STR);
        $stmt->bindValue('playtime', $app->playedTime, \PDO::PARAM_INT);
        $stmt->bindValue('review_score', $app->reviewScore, \PDO::PARAM_INT);
        $stmt->bindValue('review_amount', $app->reviewAmount, \PDO::PARAM_INT);

        $stmt->executeStatement();
    }

    public function updateReviewScore(int $appId, int $score, int $amount)
    {
        $stmt = $this->connection->prepare(
            \sprintf(
                '
                    UPDATE %s
                    SET review_score = :review_score, review_amount = :review_amount
                    WHERE app_id = :app_id;
                ',
                self::TABLE,
            ),
        );

        $stmt->bindValue('app_id', $appId, \PDO::PARAM_INT);
        $stmt->bindValue('review_score', $score, \PDO::PARAM_INT);
        $stmt->bindValue('review_amount', $amount, \PDO::PARAM_INT);

        $stmt->executeStatement();
    }

    private function map(array $result): App
    {
        return new App(
            $result['app_id'],
            $result['name'],
            $result['icon'],
            $result['playtime'],
            $result['review_score'],
            $result['review_amount'],
        );
    }
}
