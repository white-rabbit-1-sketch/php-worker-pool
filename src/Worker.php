<?php

namespace PhpWorkerPool;

use PhpWorkerPool\Queue\QueueInterface;

class Worker
{
    protected QueueInterface $queue;
    protected bool $infinite;

    public function __construct(
        QueueInterface $queue,
        bool $infinite
    ) {
        $this->queue = $queue;
        $this->infinite = $infinite;
    }

    public function start(): void
    {
        while(true) {
            while ($task = $this->queue->pop()) {
                $task->execute();
            }

            if (!$this->infinite) {
                break;
            }
        }
    }
}