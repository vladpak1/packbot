<?php
require_once __DIR__ . '/vendor/autoload.php';
if ($_GET['key'] !== PackBot\Environment::var('cronjob_key')) die('Invalid key.');


$worker = new PackBot\Worker();


$worker->doJob();
