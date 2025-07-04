<?php

namespace PackBot;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class Worker
{
    protected Telegram $telegram;

    public function __construct()
    {
        $this->initEnv();
    }

    public function queueJobs(): void
    {
        $sites = SiteMonitoringDB::getSitesIDs();
        shuffle($sites);

        $pusher = new QueuePusher();

        foreach ($sites as $siteID) {
            $pusher->pushCheck($siteID);
        }

    }

    public function doDailyJob()
    {
        $cleaner = new Cleaner();
        $cleaner->executeAll();
    }

    /**
     * Local init env.
     */
    protected function initEnv()
    {
        require_once __DIR__ . '/../../queue.php';

        try {
            $bot_api_key  = Environment::var('bot_api_key');
            $bot_username = Environment::var('bot_username');
            $db_host      = Environment::var('db_host');
            $db_user      = Environment::var('db_user');
            $db_password  = Environment::var('db_password');
            $db_name      = Environment::var('db_name');
        } catch (EnvironmentException $e) {
            echo $e->getMessage();
        }

        try {
            // Create Telegram API object
            $telegram = new Telegram($bot_api_key, $bot_username);

            $commands_paths = [
                __DIR__ . '/Commands',
            ];
            $telegram->addCommandsPaths($commands_paths);

            $telegram->enableMySql([
                'host'     => $db_host,
                'user'     => $db_user,
                'password' => $db_password,
                'database' => $db_name,
            ]);

            /**
             * Request limiter (tries to prevent reaching Telegram API limits).
             */
            if (Environment::var('enable_global_limiter')) {
                $telegram->enableLimiter();
            }

            $this->telegram = $telegram;

            //init PackDB
            PackDB::connect();
        } catch (TelegramException $e) {
            // Silence is golden!
            error_log($e->getMessage());
        }
    }
}
