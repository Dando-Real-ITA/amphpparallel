<?php declare(strict_types=1);

namespace Amp\Parallel\Worker;

use Amp\Cache\AtomicCache;
use Amp\Cancellation;
use Amp\Sync\Channel;

/**
 * A runnable unit of execution.
 *
 * @template-covariant TResult
 * @template TReceive
 * @template TSend
 * @template TCache
 */
interface Task
{
    /**
     * Executed when running the Task in a worker.
     *
     * @param Channel<TReceive, TSend> $channel Communication channel to parent process.
     * @param AtomicCache $cache AtomicCache instance shared between all Tasks executed on the Worker.
     * @param Cancellation $cancellation Tasks may safely ignore this parameter if they are not cancellable.
     *
     * @return TResult A specific type can (and should) be declared in implementing classes.
     */
    public function run(Channel $channel, AtomicCache $cache, Cancellation $cancellation): mixed;
}
