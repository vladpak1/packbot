<?php

use PackBot\Environment;

// Load composer
require __DIR__ . '/vendor/autoload.php';

try {
    $bot_api_key  = Environment::var('bot_api_key');
    $bot_username = Environment::var('bot_username');
    $hook_url     = Environment::var('hook_url');
} catch (PackBot\EnvironmentException $e) {
    echo $e->getMessage();
}

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // unet webhook
    $result = $telegram->deleteWebhook();

    if ($result->isOk()) {
        echo $result->getDescription();
    } else {
        echo 'Error: ' . $result->getDescription();
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // log telegram errors
    echo $e->getMessage();
}
