<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Domain\Model\Library;

use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\App;

final readonly class Library
{
    public array $apps;

    public function __construct(public int $appCount, App ...$apps)
    {
        $this->apps = $apps;
    }

    /** @return array<int> */
    public function appids(): array
    {
        return \array_map(static fn (App $app) => $app->appid, $this->apps);
    }

    public function app(int $id): ?App
    {
        $apps = \array_values(\array_filter(
            $this->apps,
            static fn (App $app) => $app->appid === $id,
        ));

        return $apps[0] ?? null;
    }
}
