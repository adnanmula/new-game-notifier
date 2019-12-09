<?php declare(strict_types=1);

namespace DemigrantSoft\Domain\App\Model;

final class App
{
    private int $appid;
    private string $type;
    private string $name;
    private string $header;

    private function __construct(int $appid, string $type, string $name, string $header)
    {
        $this->appid = $appid;
        $this->type = $type;
        $this->name = $name;
        $this->header = $header;
    }

    public static function create(int $appid, string $type, string $name, string $header)
    {
        return new self($appid, $type, $name, $header);
    }

    public function appid(): int
    {
        return $this->appid;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function header(): string
    {
        return $this->header;
    }
}
