<?php declare(strict_types=1);

namespace DemigrantSoft\Steam\NewGameNotifier\Domain\Service\Persistence;

interface Migration
{
    public function up(): void;
    public function down(): void;
}
