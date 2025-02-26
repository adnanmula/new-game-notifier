<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Tests\Entrypoint\Command;

use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\App;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\AppRepository;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\Library\Exception\FailedToLoadLibraryException;
use AdnanMula\Steam\NewGameNotifier\Domain\Service\Communication\CommunicationClient;
use AdnanMula\Steam\NewGameNotifier\Entrypoint\Command\CheckNewGamesCommand;
use AdnanMula\Steam\NewGameNotifier\Infrastructure\Steam\SteamClient;
use AdnanMula\Steam\NewGameNotifier\Tests\Mock\Domain\Model\LibraryObjectMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CheckNewGamesCommandTest extends TestCase
{
    private MockObject $client;
    private MockObject $communicationClient;
    private MockObject $appRepository;
    private string $userId;

    private CheckNewGamesCommand $command;

    public function setUp(): void
    {
        $this->client = $this->createMock(SteamClient::class);
        $this->communicationClient = $this->createMock(CommunicationClient::class);
        $this->appRepository = $this->createMock(AppRepository::class);
        $this->userId = '70000000';

        $this->command = new CheckNewGamesCommand(
            $this->client,
            $this->communicationClient,
            $this->appRepository,
            $this->userId,
        );
    }

    /** @test */
    public function given_telegram_disabled_then_do_not_send_group_messages(): void
    {
        $this->communicationClient->expects($this->never())->method('say');

        $app1 = new App(10, 'game1', 'icon1');
        $app2 = new App(20, 'game2', 'icon2');

        $provider = new LibraryObjectMother();
        $provider->resetApps();
        $provider->addApps($app1, $app2);
        $library = $provider->build();

        $this->client->expects($this->once())
            ->method('ownedGames')
            ->with($this->userId)
            ->willReturn($library);

        $this->appRepository->expects($this->once())
            ->method('all')
            ->willReturn([$app2->appid]);

        $this->appRepository->expects($this->once())
            ->method('save')
            ->with($app1);

        $commandTester = new CommandTester($this->command);
        $result = $commandTester->execute([]);

        $this->assertEquals(0, $result);
    }

    /** @test */
    public function given_telegram_enabled_then_send_group_messages(): void
    {
        $this->communicationClient->expects($this->exactly(2))->method('say');

        $app1 = new App(10, 'game1', 'icon1');
        $app2 = new App(20, 'game2', 'icon2');

        $provider = new LibraryObjectMother();
        $provider->resetApps();
        $provider->addApps($app1, $app2);
        $library = $provider->build();

        $this->client->expects($this->once())
            ->method('ownedGames')
            ->with($this->userId)
            ->willReturn($library);

        $this->appRepository->expects($this->once())
            ->method('all')
            ->willReturn([$library->apps[0]->appid]);

        $this->appRepository->expects($this->once())
            ->method('save')
            ->with($app2);

        $commandTester = new CommandTester($this->command);
        $result = $commandTester->execute(['-t' => 'true']);

        $this->assertEquals(0, $result);
    }

    /** @test */
    public function given_missing_games_then_sync_them(): void
    {
        $app1 = new App(10, 'game1', 'icon1');
        $app2 = new App(20, 'game2', 'icon2');
        $app3 = new App(30, 'game3', 'icon3');

        $provider = new LibraryObjectMother();
        $provider->resetApps();
        $provider->addApps($app1, $app2, $app3);
        $library = $provider->build();

        $this->client->expects($this->once())
            ->method('ownedGames')
            ->with($this->userId)
            ->willReturn($library);

        $this->appRepository->expects($this->once())
            ->method('all')
            ->willReturn([$app1->appid]);

        $app = new App(20, 'game2', 'icon2');
        $app2 = new App(30, 'game3', 'icon3');

        $this->appRepository->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive([$app], [$app2]);

        $commandTester = new CommandTester($this->command);
        $result = $commandTester->execute(['-t' => 'false']);

        $this->assertEquals(0, $result);
    }

    /** @test */
    public function given_no_missing_games_then_do_nothing(): void
    {
        $app1 = new App(10, 'game1', 'icon1');
        $app2 = new App(20, 'game2', 'icon2');
        $app3 = new App(30, 'game3', 'icon3');

        $provider = new LibraryObjectMother();
        $provider->resetApps();
        $provider->addApps($app1, $app2, $app3);
        $library = $provider->build();

        $this->client->expects($this->once())
            ->method('ownedGames')
            ->with($this->userId)
            ->willReturn($library);

        $this->appRepository->expects($this->once())
            ->method('all')
            ->willReturn([
                $app1->appid,
                $app2->appid,
                $app3->appid,
            ]);

        $this->client->expects($this->never())->method('appInfo');
        $this->appRepository->expects($this->never())->method('save');

        $commandTester = new CommandTester($this->command);
        $result = $commandTester->execute(['-t' => 'false']);

        $this->assertEquals(0, $result);
    }

    /** @test */
    public function given_bad_steam_response_then_log(): void
    {
        $this->communicationClient->expects($this->once())->method('log')->with('Fallo en GetOwnedGames');
        $this->expectException(FailedToLoadLibraryException::class);

        $this->client->expects($this->once())
            ->method('ownedGames')
            ->with($this->userId)
            ->willReturn(null);

        $this->appRepository->expects($this->never())->method('all');
        $this->client->expects($this->never())->method('appInfo');
        $this->appRepository->expects($this->never())->method('save');

        $commandTester = new CommandTester($this->command);
        $result = $commandTester->execute(['-t' => 'true']);

        $this->assertEquals(1, $result);
    }
}
