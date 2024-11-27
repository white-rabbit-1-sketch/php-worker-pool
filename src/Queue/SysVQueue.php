<?php

namespace PhpWorkerPool\Queue;

use PhpWorkerPool\TaskInterface;

class SysVQueue extends AbstractQueue
{
    protected const MAX_MESSAGE_SIZE = 1024;
    protected const MESSAGE_TYPE = 1;

    protected int $key;
    protected int $maxMessageSize;
    protected int $messageType;
    protected \SysvMessageQueue $queue;

    public function __construct(
        int $key,
        int $maxMessageSize = self::MAX_MESSAGE_SIZE,
        int $messageType = self::MESSAGE_TYPE,
    ) {
        $this->key = $key;
        $this->messageType = $messageType;
        $this->maxMessageSize = $maxMessageSize;

        $this->queue = msg_get_queue($this->key);
        if (!$this->queue) {
            throw new \RuntimeException('Unable to create or access message queue.');
        }
    }

    public function push(TaskInterface $task): void
    {
        if (!msg_send($this->queue, $this->messageType, $this->serialize($task))) {
            throw new \RuntimeException('Failed to add task to the queue.');
        }
    }

    public function pop(): ?TaskInterface
    {
        $task = null;
        $msg = null;
        if (msg_receive(
            $this->queue,
            0,
            $msgType,
            $this->maxMessageSize,
            $msg,
            true,
            MSG_IPC_NOWAIT
        )) {
            $task = $this->unserialize($msg);
        }

        return $task;
    }

    public function clear(): void
    {
        msg_remove_queue($this->queue);
        $this->queue = msg_get_queue($this->key);
    }
}
