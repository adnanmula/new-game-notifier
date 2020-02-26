<?php declare(strict_types=1);

namespace DemigrantSoft\Steam\NewGameNotifier\Infrastructure\Communication;

use DemigrantSoft\Steam\NewGameNotifier\Domain\Service\Communication\CommunicationClient;

final class TelegramClient implements CommunicationClient
{
    private \Telegram $client;
    private string $groupChatId;
    private string $adminChatId;

    public function __construct(string $token, string $groupChatId, string $adminChatId)
    {
        $this->client = new \Telegram($token);
        $this->groupChatId = $groupChatId;
        $this->adminChatId = $adminChatId;
    }

    public function say(string $msg): void
    {
        $this->client->sendMessage([
            'chat_id' => $this->groupChatId,
            'parse_mode' => 'markdown',
            'text' => $msg,
        ]);
    }

    public function log(string $msg): void
    {
        $this->client->sendMessage([
            'chat_id' => $this->adminChatId,
            'parse_mode' => 'markdown',
            'text' => $msg,
        ]);
    }
}

