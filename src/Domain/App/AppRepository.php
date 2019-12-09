<?php declare(strict_types=1);

namespace DemigrantSoft\Domain\App;

use DemigrantSoft\Domain\App\Model\App;

interface AppRepository
{
    public function app(int $appId): ?App;
    public function missing(array $appIds): array;
    public function save(App $app): void;
}
