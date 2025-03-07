<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Entrypoint\Command;

use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\AppRepository;
use AdnanMula\Steam\NewGameNotifier\Infrastructure\Completion\HltbClient;
use AdnanMula\Steam\NewGameNotifier\Infrastructure\Steam\SteamClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ImportGamesRecentCommand extends Command
{
    private const string NAME = 'steam:import:played-recent';

    public function __construct(
        private SteamClient $steamClient,
        private HltbClient $completionClient,
        private AppRepository $repository,
        private string $steamUserId,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Import recently played game time')
            ->addOption('reviews', 'r', InputOption::VALUE_NONE, 'Import app review score')
            ->addOption('completion', 'c', InputOption::VALUE_NONE, 'Import app completion data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $importReview = $input->getOption('reviews');
        $importCompletion = $input->getOption('completion');
        $apps = $this->steamClient->recentlyPlayedGames($this->steamUserId);

        foreach ($apps as $app) {
            $this->repository->updatePlaytime($app->appid, $app->playedTime);

            $output->writeln(\sprintf(
                'Updated app: %s %s | %s mins',
                \str_pad((string) $app->appid, 7, ' ', STR_PAD_LEFT),
                $app->name,
                $app->playedTime,
            ));

            if ($importReview) {
                [$score, $amount] = $this->steamClient->appReviews($app->appid);
                $this->repository->updateReviewScore($app->appid, $score, $amount);

                $output->writeln(' | Review imported');
            }

            if ($importCompletion) {
                $completionData = $this->completionClient->completionData($app->name);

                if (null === $completionData) {
                    $output->writeln(' | Cant import completion data');
                } else {
                    $this->repository->updateCompletionData($app->appid, $completionData);

                    $output->writeln(' | Completion data imported');
                }

                $output->writeln(' | Completion data imported');
            }
        }

        return self::SUCCESS;
    }
}
