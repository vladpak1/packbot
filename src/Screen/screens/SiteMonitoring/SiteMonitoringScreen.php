<?php

namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;

/**
 * @deprecated
 */
final class SiteMonitoringScreen extends Screen
{
    protected Command $command;

    protected Keyboard $keyboard;

    protected Text $text;

    protected string $screenName = 'SiteMonitoring';

    public function __construct(Command $command)
    {
        parent::__construct($command);
        $this->command = $command;
        $this->text    = new Text();
        $this->prepareKeyboard();
    }

    public function executeScreen(): ServerResponse
    {
        return $this->maybeSideExecute('Мониторинг сайта позволяет вам контролировать состояние ваших сайтов. Бот будет проверять его каждые пару минут и отправит уведомление, если с ним что-то не так.', $this->keyboard);
    }

    public function executeCallback(string $callback): ServerResponse
    {
        switch($callback) {
            default:
                error_log('An attempt to execute undefined callback for screen ' . $this->screenName . ': ' . $callback);

                return $this->sendSomethingWrong();
            case 'listSites':
                $screen = new ListSitesScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
            case 'addSite':
                $screen = new AddSiteScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
        }
    }

    protected function prepareKeyboard()
    {
        $this->keyboard = new MultiRowInlineKeyboard([
            [
                'text'          => $this->text->e('Список сайтов'),
                'callback_data' => 'SiteMonitoring_listSites',
            ],
            [
                'text'          => $this->text->e('Добавить сайт'),
                'callback_data' => 'SiteMonitoring_addSite',
            ],
            [
                'text'          => $this->text->e('Назад ⬅️'),
                'callback_data' => 'DomainChecks_backToMainMenu',
            ],
        ], -1);
    }
}
