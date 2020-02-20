<?php declare(strict_types=1);

namespace DemigrantSoft\Domain\Model\App;

final class App
{
    private int $appid;
    private string $name;
    private string $icon;
    private string $header;

    private function __construct(int $appid, string $name, string $icon, string $header)
    {
        $this->appid = $appid;
        $this->name = $name;
        $this->icon = $icon;
        $this->header = $header;
    }

    public static function create(int $appid, string $name, string $icon, string $header): self
    {
        return new self($appid, $name, $icon, $header);
    }

    public function appid(): int
    {
        return $this->appid;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function url(): string
    {
        return 'https://store.steampowered.com/app/' . $this->appid;
    }

    public function icon(): string
    {
        return $this->icon;
    }

    public function iconUrl(): string
    {
        return 'http://media.steampowered.com/steamcommunity/public/images/apps/'. $this->appid .'/'. $this->icon . '.jpg';
    }

    public function header(): string
    {
        return $this->header;
    }

    public function headerUrl(): string
    {
        return 'http://media.steampowered.com/steamcommunity/public/images/apps/'. $this->appid .'/'. $this->header . '.jpg';
    }
}
