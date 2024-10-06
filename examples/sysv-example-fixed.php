<?php

use PhpWorkerPool\ClosureTask;
use PhpWorkerPool\Pool;
use PhpWorkerPool\Queue\SysVQueue;

require_once "../vendor/autoload.php";

$queue = new SysVQueue(1234567);

$pool = new Pool($queue, infinite: false);

for ($i = 0; $i < 20; $i++) {
    $queue->push(new ClosureTask(function () {
        echo microtime() . PHP_EOL;
        sleep(1);
    }));
}

$pool->start();
$pool->wait();
$pool->stop();