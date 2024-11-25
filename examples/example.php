<?php

use PhpWorkerPool\Queue;
use PhpWorkerPool\ClosureTask;
use PhpWorkerPool\Pool;

require_once "../vendor/autoload.php";

$queue = new Queue(1234567);

for ($i = 0; $i < 20; $i++) {
    $queue->add(new ClosureTask(function () {
        echo microtime() . PHP_EOL;
        sleep(5);
    }));
}

$pool = new Pool($queue);
$pool->start();
$pool->wait();