<?php

// The function returned by this script is run by shared-memory-process.php in a separate process.
// echo, print, printf, etc. in this script are written to STDERR of the parent.
// $argc and $argv are available in this process as any other cli PHP script.

use Amp\Parallel\Sync\SharedMemoryParcel;
use function Amp\delay;

return function () use ($argv): \Generator {
    if (!isset($argv[1])) {
        throw new \Error("No parcel ID provided");
    }

    $id = $argv[1];

    \printf("Child process using parcel ID %s\n", $id);

    $parcel = SharedMemoryParcel::use($id);

    $value = $parcel->synchronized(function (int $value) {
        return $value + 1;
    });

    \printf("Value after modifying in child thread: %s\n", $value);

    delay(500); // Parent process should access parcel during this time.

    // Unwrapping the parcel now should give value from parent process.
    \printf("Value in child thread after being modified in main thread: %s\n", yield $parcel->unwrap());

    $parcel->synchronized(function (int $value) {
        return $value + 1;
    });
};
