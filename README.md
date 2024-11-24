# PHP Worker Pool via System V Message Queues

A lightweight PHP library for managing worker pools with shared memory and task queues.

## Features

- **System V Message Queues** for efficient inter-process communication.
- Simple worker pool implementation using process forking.
- Supports closures and custom task implementations via `TaskInterface`.
- Graceful handling of blocking operations.
- Lightweight and optimized for CLI usage.
- Queue easily extendable: implement custom queues with any storage system (e.g., Redis, databases) by extending the `QueueInterface`.
- Task types are extendable: simply implement the `TaskInterface` to create custom task types.


---

## Why This Pool Is Different

Unlike traditional php worker pools where new processes are spawned for each task (or copy part of queue), this pool uses a more efficient approach. Worker processes are **forked once** and then continuously process tasks from a **shared, centralized queue**. This eliminates the overhead of creating new processes for every task, significantly improving performance, especially in high-load scenarios. By reusing worker processes, this solution offers faster task processing and better resource utilization compared to conventional worker pools.


## How It Works

The library leverages **System V message queues** (using the `sysvmsg` PHP extension) to enable efficient communication between processes. Each queue is identified by a unique key and allows processes to exchange serialized messages.

### Key Features of System V Message Queues

- **Isolation**: Each queue is uniquely identified by a key, ensuring data integrity between different queues.
- **Persistence**: Queues persist in the operating system until explicitly removed or the system is rebooted.
- **Concurrency**: Multiple processes can read from and write to the queue simultaneously, making it ideal for worker pools.

### Queue Implementation

- **Adding Tasks**: Tasks are serialized and added to the queue using `msg_send`. The library ensures compatibility with closures via the `opis/closure` library, allowing complex callable structures to be safely serialized and deserialized.
- **Retrieving Tasks**: Workers fetch tasks from the queue using `msg_receive`, ensuring that each task is processed only once.


## Quick Start

## Installation

To install the PHP Worker Pool library, use Composer:

1. If you haven't already, install [Composer](https://getcomposer.org/download/) on your system.
2. Run the following command to install the library:

```bash
composer require white-rabbit-1-sketch/php-worker-pool
```

### Producer

Add tasks to the queue using `ClosureTask`:

```php
<?php

use PhpWorkerPool\Queue;

require_once "vendor/autoload.php";

// Create a queue with a unique key
$queue = new Queue(1234567);

// Add 20 tasks to the queue
for ($i = 0; $i < 20; $i++) {
    $queue->add(new \PhpWorkerPool\ClosureTask(function () {
        echo microtime() . PHP_EOL;
        sleep(5); // Simulate a long-running task
    }));
}

$pool = new \PhpWorkerPool\Pool($queue);
$pool->start();
$pool->wait();
```