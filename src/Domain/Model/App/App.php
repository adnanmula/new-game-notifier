<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Domain\Model\App;

final class App
{
    private const string URL_APP = 'https://store.steampowered.com/app/';
    private const string URL_LOGO_IMAGES = 'http://media.steampowered.com/steamcommunity/public/images/apps/';

    public function __construct(
        public readonly int $appid,
        public readonly string $name,
        public readonly string $icon,
        public readonly int $playedTime = 0,
    ) {}

    public string $url {
        get {
            return self::URL_APP . $this->appid;
        }
    }

    public string $iconUrl {
        get {
            return self::URL_LOGO_IMAGES . $this->appid . '/' . $this->icon . '.jpg';
        }
    }

    public string $headerUrl {
        get {
            return 'https://cdn.akamai.steamstatic.com/steam/apps/' . $this->appid . '/header.jpg';
        }
    }
}
