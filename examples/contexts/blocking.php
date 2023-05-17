<?php declare(strict_types=1);

// The function returned by this script is run by context.php in a separate process or thread.
// $argc and $argv are available in this process as any other cli PHP script.

use Amp\Sync\Channel;

return function (Channel $channel): int {
    printf("Received the following from parent: %s\n", $channel->receive());

    print "Sleeping for 3 seconds...\n";
    sleep(3); // Blocking call in process.

    $channel->send("Data sent from child.");

    print "Sleeping for 2 seconds...\n";
    sleep(2); // Blocking call in process.

    return 42;
};
