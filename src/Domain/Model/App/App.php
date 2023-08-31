<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Domain\Model\App;

final class App
{
    private const URL_APP = 'https://store.steampowered.com/app/';
    private const URL_IMAGES = 'http://media.steampowered.com/steamcommunity/public/images/apps/';

    private function __construct(private int $appid, private string $name, private string $icon, private string $header)
    {}

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
        return self::URL_APP . $this->appid;
    }

    public function icon(): string
    {
        return $this->icon;
    }

    public function iconUrl(): string
    {
        return self::URL_IMAGES . $this->appid . '/' . $this->icon . '.jpg';
    }

    public function header(): string
    {
        return $this->header;
    }

    public function headerUrl(): string
    {
        return self::URL_IMAGES . $this->appid . '/' . $this->header . '.jpg';
    }
}
