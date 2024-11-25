<?php

namespace PhpWorkerPool;

class Worker
{
    protected QueueInterface $queue;

    public function __construct(QueueInterface $queue)
    {
        $this->queue = $queue;
    }

    public function start(): void
    {
        while ($task = $this->queue->pop()) {
            $task->execute();
        }
    }
}