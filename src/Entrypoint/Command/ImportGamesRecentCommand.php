<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Entrypoint\Command;

use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\AppRepository;
use AdnanMula\Steam\NewGameNotifier\Infrastructure\Steam\ProtondbClient;
use AdnanMula\Steam\NewGameNotifier\Infrastructure\Steam\SteamClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'steam:import:played-recent', description: 'Import recently played game time')]
final class ImportGamesRecentCommand extends Command
{
    public function __construct(
        private SteamClient $steamClient,
        private ProtondbClient $compatibilityClient,
        private AppRepository $repository,
        private string $steamUserId,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('reviews', 'r', InputOption::VALUE_NONE, 'Import app review score')
            ->addOption('compatibility', 'c', InputOption::VALUE_NONE, 'Import app compatibility data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $importReview = $input->getOption('reviews');
        $importCompatibility = $input->getOption('compatibility');
        $apps = $this->steamClient->recentlyPlayedGames($this->steamUserId);

        foreach ($apps as $app) {
            $this->repository->updatePlaytime($app->appid, $app->playedTime);

            $output->writeln(\sprintf(
                'Updated app: %s %s | %s mins',
                \str_pad((string) $app->appid, 7, ' ', \STR_PAD_LEFT),
                $app->name,
                $app->playedTime,
            ));

            if ($importReview) {
                [$score, $amount] = $this->steamClient->appReviews($app->appid);
                $this->repository->updateReviewScore($app->appid, $score, $amount);

                $output->writeln(' | Review imported');
            }

            if ($importCompatibility) {
                $this->repository->updateCompatibilityData(
                    $app->appid,
                    $this->compatibilityClient->compatibility($app->appid),
                );
                $output->writeln(' | Compatibility data imported');
            }
        }

        return self::SUCCESS;
    }
}
