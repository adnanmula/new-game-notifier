<?php declare(strict_types=1);

namespace DemigrantSoft\Entrypoint\Command;

use DemigrantSoft\Domain\App\AppRepository;
use DemigrantSoft\Domain\Communication\CommunicationClient;
use DemigrantSoft\Infrastructure\Steam\SteamClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckNewGamesCommand extends Command
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
        $this->setDescription('Check new games added');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $ownedGames = $this->client->ownedGames($this->userId);

        if (false === isset($ownedGames['games'])) {
            return 0;
        }

        $missingApps = $this->appRepository->missing(
            \array_map(fn($game) => $game['appid'], $ownedGames['games'])
        );

        \array_walk(
            $missingApps,
            function ($missing) {
                $app = $this->client->appInfo($missing);
                if ($app) {
                    $this->appRepository->save($this->client->appInfo($missing));
                }
            }
        );

        return 0;
    }
}
