<?php

namespace Amp\Parallel\Sync;

/**
 * @param \Throwable $exception
 *
 * @return array Serializable exception backtrace, with all function arguments flattened to strings.
 */
function flattenThrowableBacktrace(\Throwable $exception): array
{
    $trace = $exception->getTrace();

    foreach ($trace as &$call) {
        unset($call['object']);
        $call['args'] = \array_map(__NAMESPACE__ . '\\flattenArgument', $call['args']);
    }

    return $trace;
}

/**
 * @param array $trace Backtrace produced by {@see formatFlattenedBacktrace()}.
 *
 * @return string
 */
function formatFlattenedBacktrace(array $trace): string
{
    $output = [];

    foreach ($trace as $index => $call) {
        if (isset($call['class'])) {
            $name = $call['class'] . $call['type'] . $call['function'];
        } else {
            $name = $call['function'];
        }

        $output[] = \sprintf(
            '#%d %s(%d): %s(%s)',
            $index,
            $call['file'] ?? '[internal function]',
            $call['line'] ?? 0,
            $name,
            \implode(', ', $call['args'])
        );
    }

    return \implode("\n", $output);
}

/**
 * @param mixed $value
 *
 * @return string Serializable string representation of $value for backtraces.
 */
function flattenArgument($value): string
{
    if ($value instanceof \Closure) {
        $closureReflection = new \ReflectionFunction($value);
        return \sprintf(
            'Closure(%s:%s)',
            $closureReflection->getFileName(),
            $closureReflection->getStartLine()
        );
    }

    if (\is_object($value)) {
        return \sprintf('Object(%s)', \get_class($value));
    }

    if (\is_array($value)) {
        return 'Array([' . \implode(', ', \array_map(__FUNCTION__, $value)) . '])';
    }

    if (\is_resource($value)) {
        return \sprintf('Resource(%s)', \get_resource_type($value));
    }

    if (\is_string($value)) {
        return '"' . $value . '"';
    }

    if (\is_null($value)) {
        return 'null';
    }

    if (\is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    return (string) $value;
}

/**
 * @param string $data Binary data.
 *
 * @return string Unprintable characters encoded as \x##.
 */
function encodeUnprintableChars(string $data): string
{
    return \preg_replace_callback("/[^\x20-\x7e]/", function (array $matches): string {
        return "\\x" . \dechex(\ord($matches[0]));
    }, $data);
}
