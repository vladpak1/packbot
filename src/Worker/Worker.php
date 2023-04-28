<?php

namespace PackBot;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Throwable;

class Worker {

    protected Telegram $telegram;

    public function __construct(){
        $this->initEnv();
    }

    public function doJob() {
        $siteChecker = new SiteChecker();

        $siteChecker->checkSites();

        $alerts = $siteChecker->getAlerts();

        echo 'Alerts: ' . count($alerts) . PHP_EOL;
        echo 'Alerts::: ' . PHP_EOL;
        print_r($alerts);

        foreach ($alerts as $alert) {
            /**
             * @var Alert $alert
             */
            sleep(2);
            echo 'Send alert: ' . $alert->getSite()->getURL() . PHP_EOL;
            $alert->send();
        }
    }



    /**
     * Local init env.
     */
    protected function initEnv() {
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
        
            $commands_paths = array(
                __DIR__ . '/Commands',
            );
            $telegram->addCommandsPaths($commands_paths);
        
            $telegram->enableMySql(array(
                'host'     => $db_host,
                'user'     => $db_user,
                'password' => $db_password,
                'database' => $db_name,
            ));
        
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