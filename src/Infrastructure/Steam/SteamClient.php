<?php declare(strict_types=1);

namespace DemigrantSoft\Infrastructure\Steam;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

final class SteamClient extends Client
{
    private const DOMAIN = 'http://api.steampowered.com/';
    private const OWNED_GAMES_ENDPOINT = 'IPlayerService/GetOwnedGames/v0001/';

    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;

        parent::__construct(['base_uri' => self::DOMAIN]);
    }

    public function ownedGames(string $userId): array
    {
        $response = $this->get(self::OWNED_GAMES_ENDPOINT, [
                RequestOptions::QUERY => [
                    'key' => $this->apiKey,
                    'steamid' => $userId,
                    'format' => 'json',
                ],
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
    }
}
