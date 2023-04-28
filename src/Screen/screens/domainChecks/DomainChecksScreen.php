<?php
namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;

final class DomainChecksScreen extends Screen {


    protected Command $command;

    protected Keyboard $keyboard;

    protected Text $text;

    protected string $screenName = 'DomainChecks';

    public function __construct(Command $command) {
        parent::__construct($command);
        $this->command = $command;
        $this->text    = new Text();
        $this->prepareKeyboard();
    }

    public function executeScreen(): ServerResponse {
        return $this->maybeSideExecute('Вы можете выполнить различные проверки домена с помощью этого бота.', $this->keyboard);
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
            case 'domainAge':
                $screen = new DomainAgeCheckScreen($this->command);
                $screen->executeScreen();
                return $this->command->getCallbackQuery()->answer();
            case 'fullWhois':
                $screen = new FullWhoisCheckScreen($this->command);
                $screen->executeScreen();
                return $this->command->getCallbackQuery()->answer();
            case 'dnsRecords':
                $screen = new DNSRecordsCheckScreen($this->command);
                $screen->executeScreen();
                return $this->command->getCallbackQuery()->answer();
        }
    }

    protected function prepareKeyboard() {
        $this->keyboard = new InlineKeyboard(
            array(
                array(
                    'text'          => $this->text->e('Возраст домена'),
                    'callback_data' => 'DomainChecks_domainAge',
                ), array(
                    'text'          => $this->text->e('Полный whois'),
    
                    'callback_data' => $this->text->e('DomainChecks_fullWhois'),
                ),
            ),
            array(
                array(
                    'text'          => $this->text->e('DNS-записи'),
                    'callback_data' => 'DomainChecks_dnsRecords',
                ),
            ),
            array(
                array(
                    'text'          => $this->text->e('Назад ⬅️'),
                    'callback_data' => 'DomainChecks_backToMainMenu',
                )
            ),
        );
    }
}
