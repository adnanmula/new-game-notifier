<?php declare(strict_types=1);

namespace AdnanMula\Steam\NewGameNotifier\Infrastructure\Completion;

final readonly class CompletionData
{
    public function __construct(
        public string $gameName,
        public int $completionMain,
        public int $completionWithExtras,
        public int $completionFull,
        public int $completionAvg,
    ) {}
}
