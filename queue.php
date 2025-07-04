<?php

// bootstrap/queue.php

declare(strict_types=1);

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher as EventDispatcher;
use Illuminate\Queue\Capsule\Manager as QueueCapsule;
use Illuminate\Database\Capsule\Manager as DBCapsule;
use Illuminate\Contracts\Queue\Factory         as QueueFactory;
use Illuminate\Contracts\Events\Dispatcher     as EventDispatcherContract;
use Illuminate\Bus\Dispatcher                  as BusDispatcher;
use Illuminate\Contracts\Bus\Dispatcher        as BusDispatcherContract;
use Illuminate\Contracts\Container\Container   as ContainerContract;
use PackBot\Environment;

require __DIR__ . '/vendor/autoload.php';

const ROOT_DIR = __DIR__;

// ─── 1) Set up the IoC container ──────────────────────────────────────────
$container = new Container();

// ─── 2) Eloquent (DB) ────────────────────────────────────────────────────
$db = new DBCapsule($container);
$db->addConnection([
    'driver'    => 'mysql',
    'host'      => Environment::var('db_host'),
    'database'  => Environment::var('db_name'),
    'username'  => Environment::var('db_user'),
    'password'  => Environment::var('db_password'),
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
], 'default');
$db->setAsGlobal();
$db->bootEloquent();

// Bind DB manager into the container so the “database” queue driver works:
$container->instance('db', $db->getDatabaseManager());
$container->instance('db.connection', $db->getConnection());

// ─── 3) Queue + Events ──────────────────────────────────────────────────
$events = new EventDispatcher($container);
$q      = new QueueCapsule($container, $events);
$q->addConnection([
    'driver'      => 'database',
    'table'       => 'jobs',
    'queue'       => 'default',
    'retry_after' => 90,
], 'default');
$q->setAsGlobal();

// Bind QueueManager and events so the worker can resolve them:
$container->instance(QueueFactory::class, $q->getQueueManager());
$container->instance(EventDispatcherContract::class, $events);

// ─── 4) Bus Dispatcher (needed by CallQueuedHandler) ─────────────────
$container->instance(
    BusDispatcherContract::class,
    new BusDispatcher($container)
);

// ─── 5) Bind the Container to its own contract ────────────────────────
$container->instance(
    ContainerContract::class,
    $container
);

// ─── 7) Make this container globally available ─────────────────────────
Container::setInstance($container);

return $container;
