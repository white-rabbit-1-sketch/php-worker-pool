<?php

namespace PhpWorkerPool;

interface QueueInterface
{
    public function add(TaskInterface $task);
    public function get(): ?TaskInterface;
}
