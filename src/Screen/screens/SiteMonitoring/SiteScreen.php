<?php

namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;

final class SiteScreen extends Screen
{
    protected Command $command;

    protected Keyboard $keyboard;

    protected Text $text;

    protected string $screenName = 'Site';

    public function __construct(Command $command)
    {
        parent::__construct($command);
        $this->command = $command;
        $this->text    = new Text();
    }

    public function executeScreen(): ServerResponse
    {
        throw new ScreenException('This screen is not intended to be executed directly.');
    }

    public function executeCallback(string $callback): ServerResponse
    {
        switch($callback) {
            default:
                error_log('An attempt to execute undefined callback for screen ' . $this->screenName . ': ' . $callback);

                return $this->sendSomethingWrong();
            case 'back':
                $screen = new ListSitesScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
        }
    }

    public function executeCallbackWithAdditionalData(string $callback, string $additionalData): ServerResponse
    {
        return match($callback) {
            default      => $this->sendSomethingWrong(),
            'siteScreen' => $this->siteScreen($additionalData),
            'deleteSite' => $this->deleteSite($additionalData),
        };
    }

    protected function siteScreen(int $siteID)
    {
        try {
            $site = new Site($siteID);
        } catch (SiteMonitoringException $e) {
            error_log('An attempt to execute site screen with undefined site id: ' . $siteID);

            return $this->sendSomethingWrong();
        }

        $message = $this->text->concatEOL(
            '[' . $site->getURL() . ']',
            '🕒 ' . $this->text->sprintf('Последняя проверка: %s', $site->getLastCheckTime()),
            '🔍 ' . $this->text->sprintf('Статус: %s', $site->getState()),
            0 == $site->getRawState() && 0 != $site->getLastCheck() ? '❗️ ' . $this->text->sprintf('Перестал работать %s.', $site->getFirstAlertSentTime()) : '',
            0 == $site->getRawState() && 0 != $site->getLastCheck() ? '▶️ ' . $this->prepareReason($site->getDownStateReason()) : '',
            (0 == $site->getLastCheck() ? 'Сайт ожидает своей первой проверки. Данные будут позже.' : ''),
        );

        $keyboard = new InlineKeyboard(
            [
                [
                    'text'          => $this->text->e('Статистика 📊'),
                    'callback_data' => 'IncidentsList_incidentsList_' . $siteID,
                ],
            ],
            [
                [
                    'text'          => $this->text->e('Удалить сайт ❌'),
                    'callback_data' => $this->screenName . '_deleteSite_' . $siteID,
                ],
                [
                    'text'          => $this->text->e('Назад ⬅️'),
                    'callback_data' => $this->screenName . '_back',
                ],
            ],
        );

        return $this->maybeSideExecute($message, $keyboard, true, [
            'disable_web_page_preview' => 'true',
        ]);
    }

    protected function deleteSite(int $siteID)
    {

        $siteManager = new SiteManager($this->getUserID());
        $siteUrl     = (new Site($siteID))->getURL();
        $siteManager->unassignOwnerFromSite($siteID);

        $screen = new ListSitesScreen($this->command);
        $screen->executeScreen();

        return $this->command->getCallbackQuery()->answer([
            'text'       => $this->text->sprintf('Сайт %s был удален. Его данные какое-то время будут оставаться в боте, так что если вы ошиблись, просто добавьте сайт снова.', $siteUrl),
            'show_alert' => true,
            'cache_time' => 5,
        ]);
    }

    protected function prepareReason(array $reason): string
    {
        return match($reason['type']) {
            default     => '',
            'wrongCode' => $this->text->sprintf('Сайт возвращает код %s.', $reason['code']),
            'timeout'   => $this->text->sprintf('Отвечает слишком долго, время ответа заняло %s сек.', $reason['timeout']),
        };
    }
}
