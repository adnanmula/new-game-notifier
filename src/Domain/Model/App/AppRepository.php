<?php declare(strict_types=1);

namespace DemigrantSoft\Steam\NewGameNotifier\Domain\Model\App;

interface AppRepository
{
    public function app(int $appId): ?App;
    /** @return int[] */
    public function all(): array;
    public function save(App $app): void;
}
