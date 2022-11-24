<?php

namespace Amp\Parallel\Test\Worker\Fixtures;

use Amp\Cache\Cache;
use Amp\Cancellation;
use Amp\Parallel\Worker\Task;
use Amp\Sync\Channel;

class CommunicatingTask implements Task
{
    public function run(Channel $channel, Cache $cache, Cancellation $cancellation): string
    {
        $channel->send('test');
        return $channel->receive($cancellation);
    }
}
