<?php

namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

final class IncidentsListScreen extends Screen
{
    protected Command $command;

    protected Text $text;

    protected string $screenName = 'IncidentsList';

    protected bool $blockSideExecute = false;

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
        }
    }

    public function blockSideExecute()
    {
        $this->blockSideExecute = true;

        return $this;
    }

    public function executeCallbackWithAdditionalData(string $callback, string $additionalData, string|false $listAction = false, int|false $currentScreen = false): ServerResponse
    {
        return match($callback) {
            default         => $this->sendSomethingWrong(),
            'back'          => $this->back($additionalData),
            'incidentsList' => $this->incidentsList($additionalData),
            'listAction'    => $this->incidentsList($additionalData, $listAction, $currentScreen),
            'listItem'      => $this->proccessIncidentItemCallback($additionalData),
        };
        //IncidentsList_listAction_siteID_typeListAction_currentPage - callback structure for list action
    }

    protected function back($siteID)
    {
        $callbackExecutor = new CallbackExecutor($this->command);

        return $callbackExecutor->forceCallback('Site_siteScreen_' . $siteID);
    }

    protected function incidentsList(int $siteID, $listAction = false, $currentPage = false): ServerResponse
    {

        $site    = new Site($siteID);
        $stat    = $this->prepareStatistics($site);
        $message = [
            '[' . $site->getURL() . ']',
            $this->text->e('📊 Статистика по сайту за последние 30 дней'),
            '',
            $this->text->sprintf('Общее количество инцидентов: %d', $stat['incidentsCount']),
            '',
            $this->text->sprintf('Uptime: %s%%', $stat['uptime']),
            $this->text->sprintf('Сбои с неправильным кодом ответа: %d', $stat['incidentsWrongCode']),
            $this->text->sprintf('Сбои с долгим ответом: %d', $stat['incidentsTimeout']),
            $this->text->sprintf('Средняя продолжительность сбоя: %s', $stat['averageIncidentsTime']),
            $this->text->sprintf('Общий даунтайм: %s', $stat['totalIncidentsTime']),
            $this->text->sprintf('Максимальная продолжительность сбоя: %s', $stat['longestIncidentTime']),
            $this->text->sprintf('Последний сбой: %s', $stat['timeSinceLastIncident']),
            '',
            $this->text->e('Выберите инцидент для просмотра подробной информации.'),
        ];

        $entries           = $this->prepareEntries($site);
        $additionalButtons = [
            [
                'text'          => $this->text->e('Назад ⬅️'),
                'callback_data' => $this->screenName . '_back_' . $siteID,
            ],
        ];

        $keyboard = new ScrollableKeyboard();
        $keyboard
        ->addEntries($entries)
        ->setKeyboardScreen($this->screenName)
        ->setPerScreen(5)
        ->setScreenItemID($siteID)
        ->setAdditionalButtons($additionalButtons);

        if ($listAction) {

            $keyboard->setCurrentPage($currentPage);

            /**
             * Do nothing if there's no next or previous page.
             */
            if ((!$keyboard->hasNextPage() && 'nextPage' == $listAction) || (!$keyboard->hasPreviousPage() && 'previousPage' == $listAction)) {
                return $this->command->getCallbackQuery()->answer();
            }

            'nextPage' == $listAction ? $keyboard->nextPage() : $keyboard->previousPage();
        }

        if ($this->blockSideExecute) {
            Request::sendMessage([
                'chat_id'                  => $this->command->getCallbackQuery()->getMessage()->getChat()->getId(),
                'text'                     => implode(PHP_EOL, $message),
                'reply_markup'             => $keyboard->getKeyboard(),
                'parse_mode'               => 'HTML',
                'disable_web_page_preview' => 'true',
            ]);

            return $this->command->getCallbackQuery()->answer();
        }

        return $this->maybeSideExecute(implode(PHP_EOL, $message), $keyboard->getKeyboard(), true, [
            'disable_web_page_preview' => 'true',
        ]);
    }

    protected function prepareStatistics(Site $site): array
    {
        $statistics = new IncidentStatistics($site);
        $time       = new Time();

        $incidentsCount       = $statistics->getIncidentsCount();
        $uptime               = $statistics->getUptime();
        $incidentsWrongCode   = $statistics->getWrongCodeIncidentsCount();
        $incidentsTimeout     = $statistics->getTimeoutIncidentsCount();
        $averageIncidentsTime = $time->secondsToHumanReadable($statistics->getAverageIncidentsTime());
        $longestIncidentTime  = $time->secondsToHumanReadable($statistics->getLongestIncidentTime());
        $totalIncidentsTime   = $time->secondsToHumanReadable($statistics->getIncidentsDuration());

        if (0 == $statistics->getIncidentsCount()) {
            $timeSinceLastIncident = $this->text->e('никогда');
        } elseif (0 === $statistics->getTimeSinceLastIncident()) {
            $timeSinceLastIncident = $this->text->e('сейчас');
        } else {
            $timeSinceLastIncident = $time->secondsToHumanReadable($statistics->getTimeSinceLastIncident()) . ' ' . $this->text->e('назад');
        }

        return [
            'incidentsCount'        => $incidentsCount,
            'uptime'                => $uptime,
            'incidentsWrongCode'    => $incidentsWrongCode,
            'incidentsTimeout'      => $incidentsTimeout,
            'averageIncidentsTime'  => $averageIncidentsTime,
            'longestIncidentTime'   => $longestIncidentTime,
            'totalIncidentsTime'    => $totalIncidentsTime,
            'timeSinceLastIncident' => $timeSinceLastIncident,
        ];
    }

    protected function prepareEntries(Site $site): array
    {
        $statistics   = new IncidentStatistics($site);
        $incidentsIDs = $statistics->getIncidentsIDs();

        $entries = [];

        foreach ($incidentsIDs as $incidentID) {
            $incident = new Incident($incidentID);

            $typeString = match($incident->getType()) {
                default     => $this->text->e('Неизвестный тип'),
                'wrongCode' => $this->text->e('Код ответа не 200'),
                'timeout'   => $this->text->e('Долгий ответ'),
            };

            $entries[] = [
                'text' => $this->text->sprintf('%s — %s', $typeString, $incident->isIncidentResolved() ? $incident->getStartTimeString() : $this->text->e('❗️продолжается')),
                'id'   => $incident->getID(),
            ];
        }

        if (empty($entries)) {
            $entries[] = [
                'text' => $this->text->e('Нет инцидентов'),
                'id'   => 'noIncidents',
            ];
        }

        return $entries;
    }

    protected function proccessIncidentItemCallback(int|string $incidentID)
    {
        if ('noIncidents' === $incidentID) {
            return $this->command->getCallbackQuery()->answer();
        }

        $callbackExecutor = new CallbackExecutor($this->command);

        return $callbackExecutor->forceCallback('Incident_incidentScreen_' . $incidentID);
    }
}
