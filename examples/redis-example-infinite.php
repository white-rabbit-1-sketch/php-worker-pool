<?php

use PhpWorkerPool\ClosureTask;
use PhpWorkerPool\Pool;
use PhpWorkerPool\Queue\RedisQueue;
use Predis\Client as RedisClient;

require_once "../vendor/autoload.php";

$redisClient = new RedisClient([
    'scheme' => 'tcp',
    'host'   => 'localhost',
    'port'   => 6379,
]);
$redisClient->connect();
$queue = new RedisQueue($redisClient, "test-queue", 123);

$pool = new Pool($queue);
$pool->start();

for ($i = 0; $i < 20; $i++) {
    $queue->push(new ClosureTask(function () {
        echo microtime() . PHP_EOL;
        sleep(1);
    }));
}

$pool->wait();
$pool->stop();
