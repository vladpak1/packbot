<?php

namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;

final class ScriptsScreen extends Screen
{
    protected Command $command;

    protected Keyboard $keyboard;

    protected Text $text;

    protected string $screenName = 'Scripts';

    public function __construct(Command $command)
    {
        parent::__construct($command);
        $this->command = $command;
        $this->text    = new Text();
        $this->prepareKeyboard();
    }

    public function executeScreen(): ServerResponse
    {
        return $this->maybeSideExecute('Здесь собраны простые скрипты, которые помогут вам выполнить ту или иную задачу.', $this->keyboard);
    }

    public function executeCallback(string $callback): ServerResponse
    {
        switch($callback) {
            default:
                error_log('An attempt to execute undefined callback for screen ' . $this->screenName . ': ' . $callback);

                return $this->sendSomethingWrong();
            case 'urlTrimmer':
                $screen = new UrlTrimmerScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
            case 'sitemapParser':
                $screen = new SitemapParserScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
        }
    }

    protected function prepareKeyboard()
    {
        $this->keyboard = new MultiRowInlineKeyboard([
            [
                'text'          => $this->text->e('Форматирование и уникализация списка ссылок'),
                'callback_data' => 'Scripts_urlTrimmer',
            ],
            [
                'text'          => $this->text->e('Парсер сайтмапов'),
                'callback_data' => 'Scripts_sitemapParser',
            ],
            [
                'text'          => $this->text->e('Назад ⬅️'),
                'callback_data' => 'DomainChecks_backToMainMenu',
            ],
        ], -1);
    }
}
