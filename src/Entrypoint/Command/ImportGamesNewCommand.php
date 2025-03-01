<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Entrypoint\Command;

use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\App;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\AppRepository;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\Library\Exception\FailedToLoadLibraryException;
use AdnanMula\Steam\NewGameNotifier\Domain\Service\Communication\CommunicationClient;
use AdnanMula\Steam\NewGameNotifier\Infrastructure\Completion\HltbClient;
use AdnanMula\Steam\NewGameNotifier\Infrastructure\Steam\SteamClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ImportGamesNewCommand extends Command
{
    private const string NAME = 'steam:import:games';

    public function __construct(
        private SteamClient $steamClient,
        private HltbClient $completionClient,
        private CommunicationClient $communicationClient,
        private AppRepository $appRepository,
        private string $steamUserId,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Import games')
            ->addOption('notifications', 't', InputOption::VALUE_NONE, 'Telegram notifications')
            ->addOption('reviews', 'r', InputOption::VALUE_NONE, 'Import app review score')
            ->addOption('completion', 'c', InputOption::VALUE_NONE, 'Import app completion data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $notificationsEnabled = $input->getOption('notifications');
        $importReview = $input->getOption('reviews');
        $importCompletion = $input->getOption('completion');

        $library = $this->steamClient->ownedGames($this->steamUserId);

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
            function (int $missing) use ($library, $importReview, $importCompletion, $output) {
                $app = $library->app($missing);

                if (null === $app) {
                    throw new FailedToLoadLibraryException();
                }

                $this->appRepository->save($app);

                $output->writeln($app->appid . ': ' . $app->name . ' saved.');

                if ($importReview) {
                    [$score, $amount] = $this->steamClient->appReviews($app->appid);
                    $this->appRepository->updateReviewScore($app->appid, $score, $amount);

                    $output->writeln(' | Review imported');
                }

                if ($importCompletion) {
                    $completionData = $this->completionClient->completionData($app->name);
                    $this->appRepository->updateCompletionData($app->appid, $completionData);

                    $output->writeln(' | Completion data imported');
                }

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
}
