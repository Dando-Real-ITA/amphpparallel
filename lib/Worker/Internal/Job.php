<?php

namespace Amp\Parallel\Worker\Internal;

use Amp\Parallel\Worker\Task;

/** @internal */
final class Job
{
    /** @var string */
    private static $nextId = 'a';

    /** @var string */
    private $id;

    /** @var Task */
    private $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
        $this->id = self::$nextId++;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTask(): Task
    {
        // Classes that cannot be autoloaded will be unserialized as an instance of __PHP_Incomplete_Class.
        if ($this->task instanceof \__PHP_Incomplete_Class) {
            throw new \Error(\sprintf("Classes implementing %s must be autoloadable by the Composer autoloader", Task::class));
        }

        return $this->task;
    }
}
