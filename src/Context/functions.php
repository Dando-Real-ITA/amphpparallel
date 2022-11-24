<?php

namespace Amp\Parallel\Context;

use Revolt\EventLoop;

/**
 * @template TResult
 * @template TReceive
 * @template TSend
 *
 * @param string|list<string> $script Path to PHP script or array with first element as path and following elements
 *     options to the PHP script (e.g.: ['bin/worker', 'Option1Value', 'Option2Value'].
 *
 * @return Context<TResult, TReceive, TSend>
 */
function startContext(string|array $script): Context
{
    return contextFactory()->start($script);
}

/**
 * Gets or sets the global context factory.
 */
function contextFactory(?ContextFactory $factory = null): ContextFactory
{
    static $map;
    $map ??= new \WeakMap();
    $driver = EventLoop::getDriver();

    if ($factory) {
        return $map[$driver] = $factory;
    }

    return $map[$driver] ??= new DefaultContextFactory();
}

/**
 * @return array Serializable exception backtrace, with all function arguments flattened to strings.
 */
function flattenThrowableBacktrace(\Throwable $exception): array
{
    $trace = $exception->getTrace();

    foreach ($trace as &$call) {
        /** @psalm-suppress InvalidArrayOffset */
        unset($call['object']);
        $call['args'] = \array_map(flattenArgument(...), $call['args'] ?? []);
    }

    return $trace;
}

/**
 * @param array $trace Backtrace produced by {@see flattenThrowableBacktrace()}.
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
            \implode(', ', $call['args'] ?? ['...'])
        );
    }

    return \implode("\n", $output);
}

/**
 * @return string Serializable string representation of $value for backtraces.
 */
function flattenArgument(mixed $value): string
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
