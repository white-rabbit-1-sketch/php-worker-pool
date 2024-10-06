<?php

namespace PhpWorkerPool;

use Opis\Closure\SerializableClosure;

class ClosureTask implements TaskInterface
{
    protected \closure|SerializableClosure $closure;

    public function __construct(\closure|SerializableClosure $closure)
    {
        $this->closure = $closure;
    }

    public function execute(): void
    {
        $this->closure->__invoke();
    }
}