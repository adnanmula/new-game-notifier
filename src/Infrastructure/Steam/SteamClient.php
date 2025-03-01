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
    private const string ENDPOINT_RECENT_PLAYED_GAMES = 'IPlayerService/GetRecentlyPlayedGames/v1/';
    private const string ENDPOINT_GAME_INFO = 'api/appdetails/';
    private const string ENDPOINT_GAME_REVIEWS = 'appreviews/%s';

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

    /** @return array<App> */
    public function recentlyPlayedGames(string $userId): array
    {
        $response = $this->steamApiClient->request(Request::METHOD_GET, self::ENDPOINT_RECENT_PLAYED_GAMES, [
            'query' => [
                'key' => $this->apiKey,
                'steamid' => $userId,
                'format' => 'json',
            ],
        ]);

        $rawResponse = Json::decode($response->getContent());

        if (false === \array_key_exists('response', $rawResponse)) {
            return [];
        }

        return \array_map(fn (array $a): App => $this->mapApp($a), $rawResponse['response']['games']);
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

        return new App(
            $rawResponse['data']['steam_appid'],
            $rawResponse['data']['type'],
            $rawResponse['data']['name'],
        );
    }

    /** @return array<int, int> */
    public function appReviews(int $appid): array
    {
        $response = $this->steamStorefrontClient->request(
            Request::METHOD_GET,
            sprintf(self::ENDPOINT_GAME_REVIEWS, $appid),
            ['query' => ['json' => 1]],
        );

        $data = Json::decode($response->getContent());

        if (1 !== $data['success']) {
            throw new \Exception('Error on app reviews fetch');
        }

        $positive = $data['query_summary']['total_positive'];
        $total = $data['query_summary']['total_reviews'];

        $score = 0;

        if (0 !== $total) {
            $score = (int) ($positive * 100 / $total);
        }

        return [$score, $total];
    }

    private function map(array $result): Library
    {
        return new Library(
            $result['game_count'],
            ...\array_map(fn (array $game) => $this->mapApp($game), $result['games']),
        );
    }

    private function mapApp(array $result): App
    {
        return new App(
            $result['appid'],
            $result['name'],
            $result['img_icon_url'],
            $result['playtime_forever'],
        );
    }
}
