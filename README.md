# PHP Worker Pool

A lightweight PHP library for managing worker pools with shared memory and task queues using **System V Message Queues** and **Redis**.

## Features

- **System V Message Queues** for efficient inter-process communication.
- **Redis Queue** for scalable, networked message handling.
- Simple worker pool implementation using process forking.
- Supports closures and custom task implementations via `TaskInterface`.
- Graceful handling of blocking operations.
- Lightweight and optimized for CLI usage.
- Queue easily extendable: implement custom queues with any storage system (e.g., Redis, databases) by extending the `QueueInterface`.
- Task types are extendable: simply implement the `TaskInterface` to create custom task types.
- Two queue options: **SysV Message Queues** and **Redis Queue**.
- Two working modes: **Infinite Loop** and **Fixed Loop** (process tasks until the queue is empty).

---

## Why This Pool Is Different

Unlike traditional PHP worker pools where new processes are spawned for each task (or copy part of queue), this pool uses a more efficient approach. Worker processes are **forked once** and then continuously process tasks from a **shared, centralized queue**. This eliminates the overhead of creating new processes for every task, significantly improving performance, especially in high-load scenarios. By reusing worker processes, this solution offers faster task processing and better resource utilization compared to conventional worker pools.

Additionally, by using **System V message queues**, tasks don't necessarily have to be sent from PHP. The queue can be accessed and tasks can be added from any other language or platform that supports System V message queues. This makes the solution highly flexible and allows seamless integration with other systems and technologies.

## Queue Types

The library provides **two queue types** that you can use to store and manage tasks:

### 1. **SysV Message Queues**
- **System V Message Queues** provide a simple, in-memory message-passing mechanism for inter-process communication. They are a great choice for tasks that need to be processed on the same system without requiring external services like Redis.
- Tasks are serialized before being added to the queue, and they are deserialized when retrieved by workers.
- **Pros**: Simple to set up, minimal dependencies (requires `sysvmsg` PHP extension), very fast for local, intra-machine communication.
- **Cons**: Limited to the local machine, not suitable for distributed systems.
- **Queue Persistence**: Queues are persistent across reboots until they are explicitly removed by the system or through code.

**Key Features**:
- **Isolation**: Each queue is uniquely identified by a key, ensuring that messages are isolated between different queues.
- **Concurrency**: Multiple worker processes can simultaneously consume tasks from the queue, making it suitable for high concurrency.

### 2. **Redis Queue**
- **Redis** is a highly scalable, distributed key-value store, often used as a message broker for queues. Redis queues allow tasks to be managed across multiple machines and can support high availability and fault tolerance.
- The library provides a simple interface to interact with Redis lists (`LPUSH` for pushing tasks and `RPOP` for pop operations).
- **Pros**: Highly scalable, networked queues, supports multiple workers across different machines, persistent storage.
- **Cons**: Requires a running Redis instance, more complex setup for distributed systems.
- **Queue Persistence**: Tasks in Redis queues persist across restarts, ensuring reliability in distributed systems.

**Key Features**:
- **Scalability**: Redis allows task queues to be managed across different machines, making it a good choice for distributed systems.
- **Redis Pub/Sub**: You can extend redis queue to get support for advanced scenarios involving integrating Redis' pub/sub features to notify workers of new tasks.

### 3. **Creating Custom Queues**

While the library comes with two built-in queue implementations (SysV and Redis), it is also flexible enough to allow you to create your own custom queue. You can implement a queue using any storage mechanism that you prefer (e.g., databases, files, etc.).

## Modes of Operation

This library supports **two operating modes** for processing tasks in the queue:

### 1. **Infinite Loop (Default Mode)**
- The worker pool will keep processing tasks **indefinitely** until it is explicitly stopped.
- Ideal for long-running processes where new tasks are added continuously to the queue.
- This mode is typically used in **daemon**-like applications.

### 2. **Fixed Loop**
- The worker pool will process a **fixed number of tasks** (until the queue is empty) and then stop.
- This mode is useful when you want the worker pool to process a set of tasks and then exit.
- For example, you may want to run a worker pool for a single job batch or when you don't want workers running indefinitely.

## How It Works

The library leverages **System V message queues** (using the `sysvmsg` PHP extension) or **Redis** to enable efficient communication between processes. Each queue is identified by a unique key and allows processes to exchange serialized messages.

### Key Features of System V Message Queues

- **Isolation**: Each queue is uniquely identified by a key, ensuring data integrity between different queues.
- **Persistence**: Queues persist in the operating system until explicitly removed or the system is rebooted.
- **Concurrency**: Multiple processes can read from and write to the queue simultaneously, making it ideal for worker pools.

### Queue Implementation

- **Adding Tasks**: Tasks are serialized and added to the queue using `msg_send` for SysV or `lpush` for Redis. The library ensures compatibility with closures via the `opis/closure` library, allowing complex callable structures to be safely serialized and deserialized.
- **Retrieving Tasks**: Workers fetch tasks from the queue using `msg_receive` for SysV or `rpop/brpop` for Redis, ensuring that each task is processed only once.

---

## Quick Start

## Installation

To install the PHP Worker Pool library, use Composer:

1. If you haven't already, install [Composer](https://getcomposer.org/download/) on your system.
2. Run the following command to install the library:

```bash
composer require white-rabbit-1-sketch/php-worker-pool
```

### Examples

Example for Sys V Queue with infinite loop:

```php
<?php

use PhpWorkerPool\ClosureTask;
use PhpWorkerPool\Pool;
use PhpWorkerPool\Queue\SysVQueue;

require_once "../vendor/autoload.php";

$queue = new SysVQueue(1234567);

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
```

Example for Redis Queue with infinite loop:

```php
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
```




Example for Sys V Queue with fixed loop:

```php
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
```

Example for Redis Queue with fixed loop:

```php
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
```