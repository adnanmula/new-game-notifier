<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Infrastructure\Completion;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HltbClient
{
    private const string ENDPOINT = '/api/ouch/86d5ef1971943765';

    public function __construct(
        private HttpClientInterface $hltbClient,
    ) {}

    public function completionData(string $gameName): ?CompletionData
    {
        $response = $this->hltbClient->request(Request::METHOD_POST, self::ENDPOINT, [
            'json' => [
                'searchType' => 'games',
                'searchTerms' => \explode(' ', \mb_strtolower($gameName)),
                'searchPage' => 1,
                'size' => 1,
                'searchOptions' => [
                    'games' => [
                        'userId' => 0,
                        'platform' => '',
                        'sortCategory' => 'popular',
                        'rangeCategory' => 'main',
                        'rangeTime' => ['min' => null, 'max' => null],
                        'gameplay' => ['perspective' => '', 'flow' => '', 'genre' => '', 'difficulty' => ''],
                        'rangeYear' => ['min' => '', 'max' => ''],
                        'modifier' => '',
                    ],
                    'users' => ['sortCategory' => 'postcount'],
                    'lists' => ['sortCategory' => 'follows'],
                    'filter' => '',
                    'sort' => 0,
                    'randomizer' => 0,
                ],
                'useCache' => true,
            ],
        ]);

        $response = $response->toArray();

        if (0 === \count($response['data'])) {
            return null;
        }

        return new CompletionData(
            $gameName,
            (int) ($response['data'][0]['comp_main'] / 60),
            (int) ($response['data'][0]['comp_plus'] / 60),
            (int) ($response['data'][0]['comp_100'] / 60),
            (int) ($response['data'][0]['comp_all'] / 60),
        );
    }
}
