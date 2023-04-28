<?php
namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;

final class ListSitesScreen extends Screen {


    protected Command $command;

    protected Keyboard $keyboard;

    protected Text $text;

    protected string $screenName = 'ListSites';

    public function __construct(Command $command) {
        parent::__construct($command);
        $this->command = $command;
        $this->text    = new Text();
        $this->prepareKeyboard();
    }

    public function executeScreen(): ServerResponse {
        $checksDisabled = Environment::var('monitoring_settings')['siteChecker']['disabled'];
        return $this->maybeSideExecute(
            'Сайты, за состоянием которых вы следите.' . ($checksDisabled ? PHP_EOL . PHP_EOL .
            $this->text->e('⚠️ Проверка сайтов временно отключена в связи с техническими работами. Скоро все заработает!') : ''),
            $this->keyboard
        );
    }

    public function executeCallback(string $callback): ServerResponse {
        switch($callback) {
            default:
                error_log('An attempt to execute undefined callback for screen ' . $this->screenName . ': ' . $callback);
                return $this->sendSomethingWrong();
            case 'back':
                $screen = new MainMenuScreen($this->command);
                $screen->executeScreen();
                return $this->command->getCallbackQuery()->answer();
            case 'addSite':
                $screen = new AddSiteScreen($this->command);
                $screen->executeScreen();
                return $this->command->getCallbackQuery()->answer();
            case 'empty':
                return $this->command->getCallbackQuery()->answer();
        }
    }

    public function executeCallbackWithAdditionalData(string $callback, string $additionalData): ServerResponse {
        return $this->sendSomethingWrong();
    }

    protected function prepareKeyboard() {


        $this->keyboard = new InlineKeyboard(array(
            array(
                'text' => $this->text->e('Добавить сайт'),
                'callback_data' => $this->screenName . '_addSite',
            ),
            array(
                'text'          => $this->text->e('Назад ⬅️'),
                'callback_data' => $this->screenName . '_back',
            ),
        ));

        $siteManager = new SiteManager($this->getUserID());
        $sites       = $siteManager->getSites();

        if (empty($sites)) {
            $this->keyboard->addRow(array(
                'text'          => $this->text->e('Вы не добавили ни одного сайта в мониторинг.'),
                'callback_data' => $this->screenName . '_empty',
            ));
            return;
        }

        foreach ($sites as $site) {
            /**
             * @var Site $site
             */
            $siteStringTemplate = $site->getRawState() !== 1 ? '❗️%s [%s, %s]' : '%s [%s, %s]';
            $this->keyboard->addRow(array(
                'text'          => $this->text->sprintf($siteStringTemplate, Format::prepDisplay($site->getURL(), 25), $this->text->e(mb_strtolower($site->getState())), $site->getLastCheckTime()),
                'callback_data' => 'Site_siteScreen_'. $site->getID(),
            ));
        }
    }
}
