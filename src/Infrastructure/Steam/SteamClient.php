<?php declare(strict_types=1);

namespace DemigrantSoft\Infrastructure\Steam;

use DemigrantSoft\Domain\App\Model\App;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

final class SteamClient extends Client
{
    private const URL_API = 'http://api.steampowered.com/';
    private const URL_STOREFRONT_API = 'https://store.steampowered.com/';
    private const ENDPOINT_OWNED_GAMES = 'IPlayerService/GetOwnedGames/v0001/';
    private const ENDPOINT_GAME_INFO = 'api/appdetails/';

    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;

        parent::__construct([]);
    }

    public function ownedGames(string $userId): array
    {
        $response = $this->get(self::URL_API . self::ENDPOINT_OWNED_GAMES, [
                RequestOptions::QUERY => [
                    'key' => $this->apiKey,
                    'steamid' => $userId,
                    'format' => 'json',
                ],
            ]
        );

        return json_decode($response->getBody()->getContents(), true)['response'];
    }

    public function appInfo(int $appid): App
    {
        $response = $this->get(self::URL_STOREFRONT_API . self::ENDPOINT_GAME_INFO, [
                RequestOptions::QUERY => [
                    'appids' => $appid,
                ],
            ]
        );
        $result = json_decode($response->getBody()->getContents(), true)[(string) $appid]['data'];

        return App::create(
            $result['steam_appid'],
            $result['type'],
            $result['name'],
            $result['header_image'],
        );
    }
}
