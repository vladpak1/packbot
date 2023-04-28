<?php
namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;

final class OtherChecksScreen extends Screen {


    protected Command $command;

    protected Keyboard $keyboard;

    protected Text $text;

    protected string $screenName = 'OtherChecks';

    public function __construct(Command $command) {
        parent::__construct($command);
        $this->command = $command;
        $this->text    = new Text();
        $this->prepareKeyboard();
    }

    public function executeScreen(): ServerResponse {
        return $this->maybeSideExecute('Здесь собраны еще несколько дополнительных инструментов.', $this->keyboard);
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
            case 'cmsCheck':
                $screen = new CmsCheckScreen($this->command);
                $screen->executeScreen();
                return $this->command->getCallbackQuery()->answer();
            case 'serverResponseCheck':
                $screen = new ServerResponseCheckScreen($this->command);
                $screen->executeScreen();
                return $this->command->getCallbackQuery()->answer();
            case 'redirectsCheck':
                $screen = new RedirectCheckScreen($this->command);
                $screen->executeScreen();
                return $this->command->getCallbackQuery()->answer();
        }
    }

    protected function prepareKeyboard() {
        $this->keyboard = new MultiRowInlineKeyboard(array(
            array(
                'text'          => $this->text->e('Проверка CMS'),
                'callback_data' => 'OtherChecks_cmsCheck',
            ),
            array(
                'text'          => $this->text->e('Проверка ответа сервера'),
                'callback_data' => 'OtherChecks_serverResponseCheck',
            ),
            array(
                'text'          => $this->text->e('Отследить редиректы'),
                'callback_data' => 'OtherChecks_redirectsCheck',
            ),
            array(
                'text'          => $this->text->e('Назад ⬅️'),
                'callback_data' => 'DomainChecks_backToMainMenu',
            ),
        ), -1);
    }
}
