<?php
namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;

final class SettingsScreen extends Screen {


    protected Command $command;

    protected Keyboard $keyboard;

    protected Text $text;

    protected string $screenName = 'SettingsChecks';

    public function __construct(Command $command) {
        parent::__construct($command);
        $this->command = $command;
        $this->text    = new Text();
        $this->prepareKeyboard();
    }

    public function executeScreen(): ServerResponse {
        return $this->maybeSideExecute('Все настройки бота собраны здесь.', $this->keyboard);
    }

    public function executeCallback(string $callback): ServerResponse {
        switch($callback) {
            default:
                error_log('An attempt to execute undefined callback for screen ' . $this->screenName . ': ' . $callback);
                return $this->sendSomethingWrong();
            case 'backToMainMenu':
                $screen = new MainMenuScreen($this->command);
                $screen->executeScreen();
                return $this->command->getCallbackQuery()->answer();
            case 'language':
                $screen = new LanguageChoiseScreen($this->command);
                $screen->executeScreen();
                return $this->command->getCallbackQuery()->answer();
        }
    }

    protected function prepareKeyboard() {
        $this->keyboard = new MultiRowInlineKeyboard(array(
            array(
                'text'          => $this->text->e('Язык бота'),
                'callback_data' => 'Settings_language',
            ),
            array(
                'text'          => $this->text->e('Назад ⬅️'),
                'callback_data' => 'Settings_backToMainMenu',
            )
        ));
    }
}
