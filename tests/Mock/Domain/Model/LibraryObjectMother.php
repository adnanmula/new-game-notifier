<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Tests\Mock\Domain\Model;

use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\App;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\Library\Library;

final class LibraryObjectMother
{
    private int $appCount;
    /** @var array<App> */
    private array $apps;

    public function __construct()
    {
        $this->appCount = 2;

        $this->addApps(
            new App(40, 'App40', 'icon-40'),
            new App(60, 'App60', 'icon-60'),
        );
    }

    public function resetApps(): self
    {
        $this->apps = [];

        return $this;
    }

    public function addApps(App ...$apps): self
    {
        foreach ($apps as $app) {
            $this->apps[] = $app;
        }

        return $this;
    }

    public function build(): Library
    {
        return new Library(
            $this->appCount,
            ...$this->apps,
        );
    }
}
