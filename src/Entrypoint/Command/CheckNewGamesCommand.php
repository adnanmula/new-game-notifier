<?php declare(strict_types=1);

namespace DemigrantSoft\Entrypoint\Command;

use DemigrantSoft\Domain\Model\App\App;
use DemigrantSoft\Domain\Model\App\AppRepository;
use DemigrantSoft\Domain\Model\Library\Exception\FailedToLoadLibraryException;
use DemigrantSoft\Domain\Service\Communication\CommunicationClient;
use DemigrantSoft\Infrastructure\Steam\SteamClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CheckNewGamesCommand extends Command
{
    private SteamClient $client;
    private CommunicationClient $communicationClient;
    private string $userId;
    private AppRepository $appRepository;

    public function __construct(
        SteamClient $client,
        CommunicationClient $communicationClient,
        AppRepository $appRepository,
        string $userId
    ) {
        $this->client = $client;
        $this->communicationClient = $communicationClient;
        $this->appRepository = $appRepository;
        $this->userId = $userId;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Check new games added')
            ->addOption('notifications', 't', InputOption::VALUE_OPTIONAL, 'Telegram notifications', false);

    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $library = $this->client->ownedGames($this->userId);

        if (null === $library) {
            $this->communicationClient->log('Fallo en GetOwnedGames');
            throw new FailedToLoadLibraryException();
        }

        $missingApps = \array_diff(
            $library->appids(),
            $this->appRepository->all()
        );

        $toNotify = \array_map(
            function (int $missing) use ($library, $output) {
                $app = $library->app($missing);

                if (null === $app) {
                    throw new FailedToLoadLibraryException();
                }

                $this->appRepository->save($app);

                $output->writeln($app->appid() . ': ' . $app->name() . ' saved.');
                return $app;
            },
            $missingApps
        );

        if ($this->notificationsEnabled($input) && \count($toNotify) > 0) {
            $this->notifyNewGames(...$toNotify);
        }

        return 0;
    }

    private function notifyNewGames(App ...$toNotify): void
    {
        $this->communicationClient->say(\count($toNotify) . ' nuevos juegos!');

        \array_walk(
            $toNotify,
            function (App $app): void {
                $this->communicationClient->say('[' . $app->name() . '](' . $app->url() . ')');
            }
        );
    }

    private function notificationsEnabled(InputInterface $input): bool
    {
        if (true === $input->hasParameterOption(['--notifications', '-t'])) {
            return $input->getOption('notifications') === 'true' || $input->getOption('notifications') === null ;
        }

        return true;
    }
}
