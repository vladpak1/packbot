<?php

// bin/queue-worker.php

require __DIR__ . '/queue.php';

use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use PackBot\LoggingExceptionHandler;

// получаем контейнер и задеплоенные сервисы
$container  = Illuminate\Container\Container::getInstance();
$manager    = $container->make(QueueFactory::class);
$events     = $container->make(DispatcherContract::class);
$exceptions = new LoggingExceptionHandler();
$isDown     = fn (): bool => false;

// создаём воркер
$worker = new Worker($manager, $events, $exceptions, $isDown);

// опции и запуск демона
$options = new WorkerOptions(3, 3, 120);
$worker->daemon('default', 'default', $options);
