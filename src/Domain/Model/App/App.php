<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Domain\Model\App;

final class App
{
    private const string URL_APP = 'https://store.steampowered.com/app/';
    private const string URL_LOGO_IMAGES = 'http://media.steampowered.com/steamcommunity/public/images/apps/';

    private function __construct(private int $appid, private string $name, private string $icon)
    {}

    public static function create(int $appid, string $name, string $icon): self
    {
        return new self($appid, $name, $icon);
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
        return self::URL_LOGO_IMAGES . $this->appid . '/' . $this->icon . '.jpg';
    }

    public function headerUrl(): string
    {
        return 'https://cdn.akamai.steamstatic.com/steam/apps/' . $this->appid . '/header.jpg';
    }
}
