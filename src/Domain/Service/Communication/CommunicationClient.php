<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Domain\Service\Communication;

interface CommunicationClient
{
    public function say(string $msg): void;
    public function log(string $msg): void;
}
