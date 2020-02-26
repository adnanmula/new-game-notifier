<?php declare(strict_types=1);

namespace DemigrantSoft\Steam\NewGameNotifier\Domain\Model\Library;

use DemigrantSoft\Steam\NewGameNotifier\Domain\Model\App\App;

final class Library
{
    private int $appCount;
    /** @var App[]  */
    private array $apps;

    private function __construct(int $appCount, App ...$apps)
    {
        $this->appCount = $appCount;
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

    /** @return array<App> */
    public function apps(): array
    {
        return $this->apps;
    }

    /** @return array<int> */
    public function appids(): array
    {
        return \array_map(static fn(App $app) => $app->appid(), $this->apps);
    }

    public function app(int $id): ?App
    {
        $app = \array_filter(
            $this->apps,
            static fn (App $app) => $app->appid() === $id
        );

        return \count($app) === 1 ? \current($app) : null;
    }
}
