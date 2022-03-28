<?php

namespace Amp\Parallel\Worker;

use function Amp\Parallel\Context\formatFlattenedBacktrace;

final class TaskFailureError extends \Error implements TaskFailureThrowable
{
    /**
     * @param string $className Original exception class name.
     * @param string $originalMessage Original exception message.
     * @param int|string $originalCode Original exception code.
     * @param array $originalTrace Backtrace generated by {@see flattenThrowableBacktrace()}.
     * @param TaskFailureThrowable|null $previous Instance representing any previous exception thrown in the Task.
     */
    public function __construct(
        private readonly string $className,
        private readonly string $originalMessage,
        private readonly int|string $originalCode,
        private readonly array $originalTrace,
        ?TaskFailureThrowable $previous = null
    ) {
        $format = 'Uncaught %s in worker with message "%s" and code "%s"; use %s::getOriginalTrace() '
            . 'for the stack trace in the worker';

        parent::__construct(
            \sprintf($format, $className, $originalMessage, $originalCode, self::class),
            0, // don't use $originalCode here due to string codes
            $previous
        );

        /** @psalm-suppress PossiblyInvalidPropertyAssignmentValue */
        $this->code = $this->originalCode;
    }

    /**
     * @return string Original exception class name.
     */
    public function getOriginalClassName(): string
    {
        return $this->className;
    }

    /**
     * @return string Original exception message.
     */
    public function getOriginalMessage(): string
    {
        return $this->originalMessage;
    }

    /**
     * @return int|string Original exception code.
     */
    public function getOriginalCode(): int|string
    {
        return $this->originalCode;
    }

    /**
     * Returns the original exception stack trace.
     *
     * @return array Same as {@see Throwable::getTrace()}, except all function arguments are formatted as strings.
     */
    public function getOriginalTrace(): array
    {
        return $this->originalTrace;
    }

    /**
     * Original backtrace flattened to a human-readable string.
     *
     * @return string
     */
    public function getOriginalTraceAsString(): string
    {
        return formatFlattenedBacktrace($this->originalTrace);
    }
}
