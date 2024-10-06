<?php

namespace PhpWorkerPool;

use PhpWorkerPool\Queue\QueueInterface;

class Pool
{
    protected const POOL_SIZE = 10;

    protected QueueInterface $queue;
    protected int $poolSize;
    protected bool $infinite;
    protected array $workers = [];

    public function __construct(
        QueueInterface $queue,
        int $poolSize = self::POOL_SIZE,
        bool $infinite = true
    ) {
        $this->poolSize = $poolSize;
        $this->queue = $queue;
        $this->infinite = $infinite;
    }

    public function start(): void
    {
        while (count($this->workers) < $this->poolSize) {
            $pid = pcntl_fork();

            if ($pid == -1) {
                throw new \RuntimeException('Unable to fork process.');
            } elseif ($pid === 0) {
                $worker = new Worker($this->queue, $this->infinite);
                $worker->start();

                exit();
            } else {
                $this->workers[] = $pid;
            }
        }
    }

    public function wait(): void
    {
        foreach ($this->workers as $pid) {
            pcntl_waitpid($pid, $status);
        }
    }

    public function stop(): void
    {
        foreach ($this->workers as $pid) {
            posix_kill($pid, SIGTERM);
        }
    }
}

