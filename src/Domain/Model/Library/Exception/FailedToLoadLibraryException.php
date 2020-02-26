<?php declare(strict_types=1);

namespace DemigrantSoft\Steam\NewGameNotifier\Domain\Model\Library\Exception;

use Symfony\Component\HttpFoundation\Response;

final class FailedToLoadLibraryException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Failed to load library.', Response::HTTP_NOT_FOUND);
    }
}
