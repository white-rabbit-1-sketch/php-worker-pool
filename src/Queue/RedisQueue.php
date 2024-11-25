<?php

namespace PhpWorkerPool\Queue;

use PhpWorkerPool\TaskInterface;
use Predis\Client as RedisClient;

class RedisQueue extends AbstractQueue
{
    protected RedisClient $redis;
    protected string $queueName;
    protected $semaphore;

    public function __construct(
        RedisClient $redis,
        string $queueName,
        int $semaphoreKey
    ) {
        $this->queueName = $queueName;
        $this->redis = $redis;

        $this->semaphore = sem_get($semaphoreKey);
        if (!$this->semaphore) {
            throw new \RuntimeException('Failed to create semaphore.');
        }
    }

    public function push(TaskInterface $task): void
    {
        sem_acquire($this->semaphore);
        try {
            $this->redis->lpush($this->queueName, [$this->serialize($task)]);
        } finally {
            sem_release($this->semaphore);
        }
    }

    public function pop(): ?TaskInterface
    {
        sem_acquire($this->semaphore);
        try {
            $task = $this->redis->rpop($this->queueName);
            $task = $task ? $this->unserialize($task) : null;
        } finally {
            sem_release($this->semaphore);
        }

        return $task;
    }

    public function clear(): void
    {
        sem_acquire($this->semaphore);
        try {
            $this->redis->del($this->queueName);
        } finally {
            sem_release($this->semaphore);
        }
    }
}
