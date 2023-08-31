<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Entrypoint\Command;

use AdnanMula\Steam\NewGameNotifier\Domain\Service\Persistence\Migration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class InitDbCommand extends Command
{
    /** @var array<Migration> */
    private array $migrations;

    public function __construct(Migration ...$migration)
    {
        parent::__construct(null);

        $this->migrations = $migration;
    }

    protected function configure(): void
    {
        $this->setDescription('Init database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        \array_walk(
            $this->migrations,
            static function (Migration $migration) use ($output): void {
                $migration->down();
                $migration->up();

                $output->writeln($migration::class . ' executed');
            },
        );

        return self::SUCCESS;
    }
}
