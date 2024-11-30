<?php

namespace PhpWorkerPool\Test;

use PHPUnit\Framework\TestCase;
use PhpWorkerPool\ClosureTask;
use PhpWorkerPool\Pool;
use PhpWorkerPool\Queue\SysVQueue;

class PoolTest extends TestCase
{
    protected const QUEUE_KEY = 1234567;

    public function testPool(): void
    {
        $queue = new SysVQueue(self::QUEUE_KEY);

        $filePaths = [];
        for ($i = 0; $i < 20; $i++) {
            $tempFilePath = tempnam(sys_get_temp_dir(), 'worker-pool.data');
            $queue->push(new ClosureTask(function () use ($tempFilePath) {
                file_put_contents($tempFilePath, '1');
            }));
            $filePaths[] = $tempFilePath;
        }

        $pool = new Pool($queue, infinite: false);
        $pool->start();
        $pool->wait();
        $pool->stop();

        foreach ($filePaths as $filePath) {
            $this->assertEquals('1', file_get_contents($filePath));
        }
    }
}