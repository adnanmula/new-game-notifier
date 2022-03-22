<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Domain\Model\App;

interface AppRepository
{
    public function app(int $appId): ?App;
    /** @return array<int> */
    public function all(): array;
    public function save(App $app): void;
}
