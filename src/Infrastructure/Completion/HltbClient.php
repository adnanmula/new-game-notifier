<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Infrastructure\Completion;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HltbClient
{
    private const string ENDPOINT = '/api/ouch/';

    public function __construct(
        private HttpClientInterface $hltbClient,
        private string $hltbToken,
    ) {}

    public function completionData(string $gameName): ?CompletionData
    {
        $searchTerms = $this->formatName($gameName);

        if (null === $searchTerms) {
            return null;
        }

        $response = $this->hltbClient->request(Request::METHOD_POST, self::ENDPOINT . $this->hltbToken, [
            'json' => [
                'searchType' => 'games',
                'searchTerms' => $searchTerms,
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

    private function formatName(string $name): ?array
    {
        $name = \mb_strtolower($name);

        $toIgnore = [
            'demo',
            'test server',
            'public test',
            'open beta',
            'playable teaser',
        ];

        foreach ($toIgnore as $ignore) {
            if (\str_ends_with($name, $ignore)) {
                return null;
            }
        }

        $toRemove = [
            '™',
            '®',
            '©',
            ':',
            '&',
            '(classic, 2005)',
            '(1994 Classic Edition)',
            'definitive edition',
            'complete edition',
            'deluxe edition',
            'remastered',
            'emperor edition',
            'goty edition',
            'game of the year edition',
            'gold edition',
        ];

        $name = \str_replace($toRemove, '', $name);
        $name = \str_replace([' - '], ' ', $name);

        $name = \explode(' ', \mb_strtolower($name));

        $lastElement = \end($name);

        if (\str_starts_with($lastElement, '(') && \str_ends_with($lastElement, ')')) {
            \array_pop($name);
        }

        return $name;
    }
}
