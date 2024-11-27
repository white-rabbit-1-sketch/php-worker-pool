<?php

namespace PhpWorkerPool\Queue;

abstract class AbstractQueue implements QueueInterface
{
    protected function serialize($data): string
    {
        return  \Opis\Closure\serialize($data);
    }

    protected function unserialize(string $data): mixed
    {
        return \Opis\Closure\unserialize($data);
    }
}