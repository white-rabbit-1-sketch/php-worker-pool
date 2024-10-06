<?php

namespace PhpWorkerPool\Queue;

use PhpWorkerPool\TaskInterface;

interface QueueInterface
{
    public function push(TaskInterface $task);
    public function pop(): ?TaskInterface;
    public function clear(): void;
}
