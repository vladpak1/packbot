<?php

require __DIR__ . '/vendor/autoload.php';
use Illuminate\Queue\Capsule\Manager as QueueCapsule;

require_once __DIR__ . '/queue.php';

QueueCapsule::connection('default')->push(new \PackBot\CheckSiteJob(2653));
