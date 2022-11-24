<?php

namespace Amp\Parallel\Worker\Internal;

use Amp\Cancellation;
use Amp\Parallel\Worker\Execution;
use Amp\Parallel\Worker\Task;
use Amp\Parallel\Worker\Worker;

/** @internal */
final class PooledWorker implements Worker
{
    /**
     * @param Closure(Worker):void $push Callable to push the worker back into the queue.
     */
    public function __construct(
        private readonly Worker $worker,
        private readonly \Closure $push,
    ) {
    }

    /**
     * Automatically pushes the worker back into the queue.
     */
    public function __destruct()
    {
        ($this->push)($this->worker);
    }

    public function isRunning(): bool
    {
        return $this->worker->isRunning();
    }

    public function isIdle(): bool
    {
        return $this->worker->isIdle();
    }

    public function submit(Task $task, ?Cancellation $cancellation = null): Execution
    {
        $job = $this->worker->submit($task, $cancellation);

        // Retain a reference to $this to prevent premature release of worker.
        $job->getResult()->finally(fn () => $this)->ignore();

        return $job;
    }

    public function shutdown(): void
    {
        $this->worker->shutdown();
    }

    public function kill(): void
    {
        $this->worker->kill();
    }
}
