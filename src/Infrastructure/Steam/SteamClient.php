<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Infrastructure\Steam;

use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\App;
use AdnanMula\Steam\NewGameNotifier\Domain\Model\Library\Library;
use AdnanMula\Steam\NewGameNotifier\Shared\Json;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SteamClient
{
    private const string ENDPOINT_OWNED_GAMES = 'IPlayerService/GetOwnedGames/v0001/';
    private const string ENDPOINT_GAME_INFO = 'api/appdetails/';

    public function __construct(
        private string $apiKey,
        private HttpClientInterface $steamApiClient,
        private HttpClientInterface $steamStorefrontClient,
    ) {}

    public function ownedGames(string $userId): ?Library
    {
        $response = $this->steamApiClient->request(Request::METHOD_GET, self::ENDPOINT_OWNED_GAMES, [
            'query' => [
                'key' => $this->apiKey,
                'steamid' => $userId,
                'format' => 'json',
                'include_appinfo' => true,
            ],
        ]);

        $rawResponse = Json::decode($response->getContent());

        if (false === \array_key_exists('response', $rawResponse)) {
            return null;
        }

        return $this->map($rawResponse['response']);
    }

    public function appInfo(int $appid): ?App
    {
        $response = $this->steamStorefrontClient->request(Request::METHOD_GET, self::ENDPOINT_GAME_INFO, [
            'query' => [
                'appids' => $appid,
            ],
        ]);

        $rawResponse = Json::decode($response->getContent())[(string) $appid];

        if (false === $rawResponse['success']) {
            return null;
        }

        return App::create(
            $rawResponse['data']['steam_appid'],
            $rawResponse['data']['type'],
            $rawResponse['data']['name'],
        );
    }

    private function map(array $result): Library
    {
        return Library::create(
            $result['game_count'],
            ...\array_map(fn (array $game) => $this->mapApp($game), $result['games']),
        );
    }

    private function mapApp(array $result): App
    {
        return App::create(
            $result['appid'],
            $result['name'],
            $result['img_icon_url'],
        );
    }
}
