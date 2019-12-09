<?php declare(strict_types=1);

namespace DemigrantSoft\Domain\Communication;

interface CommunicationClient
{
    public function say(string $msg): void;
    public function log(string $msg): void;
}
