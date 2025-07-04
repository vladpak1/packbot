<?php
/**
 * Query this file to do job.
 * Run this file every minute or so.
 */
require_once __DIR__ . '/vendor/autoload.php';

//if ($_GET['key'] !== PackBot\Environment::var('cronjob_key')) {
//    die('Invalid key.');
//}

$worker = new PackBot\Worker();

$worker->queueJobs();
