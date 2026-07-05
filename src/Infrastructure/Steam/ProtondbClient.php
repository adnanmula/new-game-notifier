<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Infrastructure\Steam;

use AdnanMula\Steam\NewGameNotifier\Domain\Model\App\AppCompatibility;
use AdnanMula\Steam\NewGameNotifier\Shared\Json;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class ProtondbClient
{
    private const string ENDPOINT_SUMMARY = '/api/v1/reports/summaries/%s.json';

    public function __construct(
        private HttpClientInterface $protondbClient,
    ) {}

    public function compatibility(int $appId): AppCompatibility
    {
        try {
            $response = $this->protondbClient->request(Request::METHOD_GET, sprintf(self::ENDPOINT_SUMMARY, $appId));
        } catch (\Throwable) {
            return AppCompatibility::UNKNOWN;
        }

        $rawResponse = Json::decode($response->getContent());

        if (false === \array_key_exists('tier', $rawResponse)) {
            return AppCompatibility::PENDING;
        }

        return AppCompatibility::from($rawResponse['tier']);
    }
}
