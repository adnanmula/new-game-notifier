<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Domain\Model\Library;

use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\App;

final class Library
{
    private array $apps;

    private function __construct(private int $appCount, App ...$apps)
    {
        $this->apps = $apps;
    }

    public static function create(int $appCount, App ...$apps): self
    {
        return new self($appCount, ...$apps);
    }

    public function appCount(): int
    {
        return $this->appCount;
    }

    public function apps(): array
    {
        return $this->apps;
    }

    /** @return array<int> */
    public function appids(): array
    {
        return \array_map(static fn (App $app) => $app->appid(), $this->apps);
    }

    public function app(int $id): ?App
    {
        $apps = \array_values(\array_filter(
            $this->apps,
            static fn (App $app) => $app->appid() === $id,
        ));

        return $apps[0] ?? null;
    }
}
