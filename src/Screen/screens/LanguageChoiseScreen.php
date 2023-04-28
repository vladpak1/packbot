<?php
namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;

final class LanguageChoiseScreen extends Screen {


    protected Command $command;

    protected Keyboard $keyboard;

    protected Text $text;

    protected string $screenName = 'LanguageChoise';

    public function __construct(Command $command) {
        parent::__construct($command);
        $this->command = $command;
        $this->prepareKeyboard();
        $this->text = new Text();
    }

    public function executeScreen(): ServerResponse {
        return $this->maybeSideExecute('Choose the language I will speak with you in.', $this->keyboard);
    }

    public function executeCallback(string $callback): ServerResponse {
        switch($callback) {
            default:
                error_log('An attempt to execute undefined callback for screen ' . $this->screenName . ': ' . $callback);
                return $this->sendSomethingWrong();
            case 'setSettingsLanguageEN':
                $settings = new UserSettings();
                $settings->set('language', 'en_US');
                $screen = new MainMenuScreen($this->command);
                $screen->executeScreen();
                return $this->command->getCallbackQuery()->answer([
                    'text'       => 'I will speak with you in English!',
                    'show_alert' => false,
                    'cache_time' => 5,
                ]);
            case 'setSettingsLanguageRU':
                $settings = new UserSettings();
                $settings->set('language', 'ru_RU');
                $screen = new MainMenuScreen($this->command);
                $screen->executeScreen();
                return $this->command->getCallbackQuery()->answer([
                    'text'       => 'Буду говорить с вами на русском!',
                    'show_alert' => false,
                    'cache_time' => 5,
                ]);
        }
    }

    protected function prepareKeyboard() {
        $this->keyboard = $inlineKeyboard = new InlineKeyboard(array(
            array(
                'text' => 'English',
                'callback_data' => 'LanguageChoise_setSettingsLanguageEN',
            ),
            array(
                'text' => 'Русский',
                'callback_data' => 'LanguageChoise_setSettingsLanguageRU',
            ),
        ));

    }

}
