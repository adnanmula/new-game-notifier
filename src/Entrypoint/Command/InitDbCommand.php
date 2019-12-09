<?php declare(strict_types=1);

namespace DemigrantSoft\Entrypoint\Command;

use DemigrantSoft\Domain\Persistence\Repository\Migration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class InitDbCommand extends Command
{
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->migrations as $migration) {
            $migration->down();
            $migration->up();

            $output->writeln(get_class($migration) . ' executed');
        }

        return 0;
    }
}
