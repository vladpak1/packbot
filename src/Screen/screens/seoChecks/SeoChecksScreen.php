<?php
namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;

final class SeoChecksScreen extends Screen {


    protected Command $command;

    protected Keyboard $keyboard;

    protected Text $text;

    protected string $screenName = 'SeoChecks';

    public function __construct(Command $command) {
        parent::__construct($command);
        $this->command = $command;
        $this->text    = new Text();
        $this->prepareKeyboard();
    }

    public function executeScreen(): ServerResponse {
        return $this->maybeSideExecute('Здесь собраны инструменты для проверки SEO.', $this->keyboard);
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
            case 'checkIndexPossibility':
                $screen = new IndexPossibilityCheckScreen($this->command);
                $screen->executeScreen();
                return $this->command->getCallbackQuery()->answer();
        }
    }

    protected function prepareKeyboard() {
        $this->keyboard = new MultiRowInlineKeyboard(array(
            array(
                'text' => $this->text->e('Проверка возможности индексации'),
                'callback_data' => $this->screenName . '_checkIndexPossibility',
            ),
            array(
                'text'          => $this->text->e('Назад ⬅️'),
                'callback_data' => $this->screenName . '_backToMainMenu',
            ),
        ), -1);
    }
}
