<?php declare(strict_types=1);

namespace Amp\Parallel\Worker;

use Amp\Parallel\Context\Internal;

final class TaskFailureException extends \Exception implements TaskFailureThrowable
{
    use Internal\ContextException;

    protected function invokeExceptionConstructor(string $message, ?\Throwable $previous): void
    {
        parent::__construct($message, 0, $previous);
    }
}
