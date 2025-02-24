<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Infrastructure\Communication;

use AdnanMula\Steam\NewGameNotifier\Domain\Service\Communication\CommunicationClient;
use AdnanMula\Telegram\SendMessage\TelegramClient as Client;

final class TelegramClient implements CommunicationClient
{
    private Client $client;

    public function __construct(string $telegramToken, private string $groupChatId, private string $adminChatId)
    {
        $this->client = new Client($telegramToken);
    }

    public function say(string $msg): void
    {
        $this->client->sendMessage($this->groupChatId, $msg);
    }

    public function log(string $msg): void
    {
        $this->client->sendMessage($this->adminChatId, $msg);
    }
}
