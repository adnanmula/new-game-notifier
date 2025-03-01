<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Domain\Model\App;

use AdnanMula\Criteria\Criteria;
use AdnanMula\Steam\NewGameNotifier\Infrastructure\Completion\CompletionData;

interface AppRepository
{
    public function app(int $appId): ?App;
    /** @return array<int> */
    public function all(): array;
    /** @return array<App> */
    public function search(Criteria $criteria): array;
    public function searchOne(Criteria $criteria): ?App;
    public function save(App $app): void;
    public function updatePlaytime(int $appId, int $amount);
    public function updateReviewScore(int $appId, int $score, int $amount);
    public function updateCompletionData(int $appId, CompletionData $completionData);
}
