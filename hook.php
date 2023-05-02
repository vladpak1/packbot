<?php

use Longman\TelegramBot\Exception\TelegramException;
use PackBot\Environment;
use PackBot\PackDB;
use PackBot\Path;

// Load composer
require __DIR__ . '/vendor/autoload.php';

try {
    $bot_api_key  = Environment::var('bot_api_key');
    $bot_username = Environment::var('bot_username');
    $hook_url     = Environment::var('hook_url');
    $db_host      = Environment::var('db_host');
    $db_user      = Environment::var('db_user');
    $db_password  = Environment::var('db_password');
    $db_name      = Environment::var('db_name');
} catch (PackBot\EnvironmentException $e) {
    error_log($e->getMessage());
}

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

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

    $telegram->setDownloadPath(Path::toTemp());

    // Logging (Error, Debug and Raw Updates)
    // https://github.com/php-telegram-bot/core/blob/master/doc/01-utils.md#logging

    if (Environment::var('logging')) {
        //delete all logs every request

        try {
            @unlink(__DIR__ . '/php-telegram-bot-debug.log');
            @unlink(__DIR__ . '/php-telegram-bot-error.log');
            @unlink(__DIR__ . '/php-telegram-bot-update.log');

        } catch (Throwable) {

        }

        \Longman\TelegramBot\TelegramLog::$always_log_request_and_response = true;

        Longman\TelegramBot\TelegramLog::initialize(
        new Monolog\Logger('telegram_bot', [
            (new Monolog\Handler\StreamHandler(__DIR__ . '/php-telegram-bot-debug.log', Monolog\Logger::DEBUG))->setFormatter(new Monolog\Formatter\LineFormatter(null, null, true)),
            (new Monolog\Handler\StreamHandler(__DIR__ . '/php-telegram-bot-error.log', Monolog\Logger::ERROR))->setFormatter(new Monolog\Formatter\LineFormatter(null, null, true)),
        ]),
        new Monolog\Logger('telegram_bot_updates', [
            (new Monolog\Handler\StreamHandler(__DIR__ . '/php-telegram-bot-update.log', Monolog\Logger::INFO))->setFormatter(new Monolog\Formatter\LineFormatter('%message%' . PHP_EOL)),
        ])
        );
    }

    $telegram->enableAdmins(
        Environment::var('admins'),
    );

    /**
     * Request limiter (tries to prevent reaching Telegram API limits).
     */
    if (Environment::var('enable_global_limiter')) {
        $telegram->enableLimiter();
    }


    //init PackDB
    PackDB::connect();


    // Handle telegram webhook request
    $telegram->handle();
} catch (TelegramException $e) {
    // Silence is golden!
    error_log($e->getMessage());
}