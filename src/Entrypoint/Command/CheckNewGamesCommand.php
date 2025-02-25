<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Entrypoint\Command;

use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\App;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\AppRepository;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\Library\Exception\FailedToLoadLibraryException;
use AdnanMula\Steam\NewGameNotifier\Domain\Service\Communication\CommunicationClient;
use AdnanMula\Steam\NewGameNotifier\Infrastructure\Steam\SteamClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CheckNewGamesCommand extends Command
{
    private const string NAME = 'new-game-notifier:check';

    public function __construct(
        private SteamClient $client,
        private CommunicationClient $communicationClient,
        private AppRepository $appRepository,
        private string $steamUserId,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Check new games added')
            ->addOption('notifications', 't', InputOption::VALUE_OPTIONAL, 'Telegram notifications', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $notificationsEnabled = $this->notificationsEnabled($input);
        $library = $this->client->ownedGames($this->steamUserId);

        if (null === $library) {
            if ($notificationsEnabled) {
                $this->communicationClient->log('Fallo en GetOwnedGames');
            }

            throw new FailedToLoadLibraryException();
        }

        $missingApps = \array_diff(
            $library->appids(),
            $this->appRepository->all(),
        );

        $toNotify = \array_map(
            function (int $missing) use ($library, $output) {
                $app = $library->app($missing);

                if (null === $app) {
                    throw new FailedToLoadLibraryException();
                }

                $this->appRepository->save($app);

                $output->writeln($app->appid . ': ' . $app->name . ' saved.');

                return $app;
            },
            $missingApps,
        );

        if ($notificationsEnabled && \count($toNotify) > 0) {
            $this->notifyNewGames(...$toNotify);
        }

        return self::SUCCESS;
    }

    private function notifyNewGames(App ...$toNotify): void
    {
        $this->communicationClient->say(\count($toNotify) . ' nuevos juegos!');

        \array_walk(
            $toNotify,
            function (App $app): void {
                $this->communicationClient->say('[' . $app->name . '](' . $app->url . ')');
            },
        );
    }

    private function notificationsEnabled(InputInterface $input): bool
    {
        if (true === $input->hasParameterOption(['--notifications', '-t'])) {
            return 'true' === $input->getOption('notifications') || null === $input->getOption('notifications');
        }

        return true;
    }
}
