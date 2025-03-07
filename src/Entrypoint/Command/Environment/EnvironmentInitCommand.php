<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Entrypoint\Command\Environment;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DatabaseDoesNotExist;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class EnvironmentInitCommand extends Command
{
    private const string NAME = 'environment:init';

    public function __construct(
        private readonly Connection $defaultConnection,
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Initialize environment (creates db and executes migrations)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $app = $this->getApplication();

        if (null === $app) {
            throw new \RuntimeException('Kernel not initialized');
        }

        $app->setAutoExit(false);

        $this->database($output);
        $this->migrations($output);

        return Command::SUCCESS;
    }

    private function database(OutputInterface $output): void
    {
        $dbName = $this->databaseName($this->connection->getParams()['url'] ?? null);

        try {
            $this->defaultConnection->createSchemaManager()->dropDatabase($dbName);
        } catch (DatabaseDoesNotExist) {
        }

        $this->defaultConnection->createSchemaManager()->createDatabase($dbName);

        $output->writeln('Database created.');
    }

    private function databaseName(?string $url): string
    {
        if (null === $url) {
            throw new \Exception('Invalid connection dns');
        }

        $parsedUrl = \parse_url($url);
        $path = $parsedUrl['path'] ?? null;

        if (null === $path) {
            throw new \Exception('Invalid connection dns');
        }

        return \ltrim($path, '/');
    }

    private function migrations(OutputInterface $output): void
    {
        $phinx = new PhinxApplication();
        $command = $phinx->find('migrate');

        $arguments = [
            'command' => 'migrate',
        ];

        $command->run(new ArrayInput($arguments), $output);

        $output->writeln('Migrations executed.');
    }
}
