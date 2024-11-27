<?php

namespace PhpWorkerPool;

interface TaskInterface
{
    public function execute(): void;
}
