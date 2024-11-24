<?php

namespace PhpWorkerPool;

class Queue implements QueueInterface
{
    protected const MAX_MESSAGE_SIZE = 1024;
    protected const MESSAGE_TYPE = 1;

    protected int $key;
    protected int $maxMessageSize;
    protected int $messageType;
    protected \SysvMessageQueue $queue;

    public function __construct(
        int $key,
        bool $recreateQueue = true,
        int $maxMessageSize = self::MAX_MESSAGE_SIZE,
        int $messageType = self::MESSAGE_TYPE,
    ) {
        $this->key = $key;
        $this->messageType = $messageType;
        $this->maxMessageSize = $maxMessageSize;

        if (msg_queue_exists($this->key) && $recreateQueue) {
            msg_remove_queue(msg_get_queue($this->key));
        }

        $this->queue = msg_get_queue($this->key);
        if (!$this->queue) {
            throw new \RuntimeException('Unable to create or access message queue.');
        }
    }

    public function add(TaskInterface $task): void
    {
        $serializedTask = \Opis\Closure\serialize($task);
        if (!msg_send($this->queue, $this->messageType, $serializedTask)) {
            throw new \RuntimeException('Failed to add task to the queue.');
        }
    }

    public function get(): ?TaskInterface
    {
        $taskData = null;

        if (msg_receive(
            $this->queue, 0,
            $msgType,
            $this->maxMessageSize,
            $taskData,
            true
        )) {
            $task = \Opis\Closure\unserialize($taskData);

            return $task;
        }

        return null;
    }

    public function __destruct()
    {
        msg_remove_queue($this->queue);
    }
}
