<?php

namespace PackBot;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;

class Alert {

    use TimeTrait;

    /**
     * Construct Alert data.
     * @var int $siteID The ID of the site that triggered the alert.
     * @var array|false $reason The reason for the alert. If false, the alert is a recovery alert.
     * @var string $alertType The type of the alert. Can be 'firstAlert', 'anotherAlert' or 'workingAgain'.
     */
    protected array $data;

    /**
     * The users that are assigned to the site.
     * @var array $assignedUsers Array with user IDs.
     */
    protected array $assignedUsers = array();

    /**
     * The type of the alert. Can be 'firstAlert', 'anotherAlert' or 'workingAgain'.
     * @var string $alertType
     */
    protected string $alertType;

    /**
     * The text of the alert.
     */
    protected Text $text;

    /**
     * This class is representing an alert that is sent to the assigned users.
     * Alerts are sent to users when a site is down or when it's working again.
     * 
     * The monitoring architecture assumes that one site has several owners,
     * so the scheme for sending alerts is organized as follows.
     * 
     * @param array $data The data of the alert.
     * @var int $data['site_id'] The ID of the site that triggered the alert.
     * @var array|false $data['reason'] The reason for the alert. If false, the alert is a recovery alert.
     * @var string $data['alert_type'] The type of the alert. Can be 'firstAlert', 'anotherAlert' or 'workingAgain'.
     */
    public function __construct(array $data) {
        $this->data      = $data;
        $this->alertType = $this->getAlertType();
        $this->getAssignedUsers();
    }
    
    /**
     * Get site ID.
     */
    public function getSiteID(): int {
        return $this->data['site_id'];
    }

    /**
     * Get site.
     */
    public function getSite(): Site {
        return new Site($this->getSiteID());
    }

    /**
     * Get reason.
     * @return array|false The reason for the alert as an array. If false, the alert is a recovery alert.
     */
    public function getReason(): array|false {
        return $this->data['reason'];
    }

    /**
     * Get alert type.
     */
    public function getAlertType(): string {
        return $this->data['alert_type'];
    }

    /**
     * Send the alerts.
     * It is not necessary means that the alert will be sent to all users.
     */
    public function send(): void {
        if (empty($this->assignedUsers)) {
            echo 'No assigned users' . PHP_EOL;
            return;
        }

        switch($this->alertType) {
            case 'firstAlert':
                $incident = IncidentFactory::createIncident($this->getSiteID(), $this->getReason());
                SiteMonitoringDB::updateSiteData(array(
                    'firstAlertSent'             => time(),
                    'lastAlertSent'              => time(),
                    'alertCount'                 => 1,
                    'currentIssueAlertsDisabled' => array(),
                    'reason'                     => $this->getReason(),
                    'incidentID'                 => $incident->getID()
                ), $this->getSiteID());
                break;
            case 'anotherAlert':
                /**
                 * Check if required time since last alert has passed.
                 */
                $site = new Site($this->getSiteID());
                $lastAlertSentTimestamp = $site->getLastAlertTimestamp();

                $minsPassed    = $this->getTimestampsDifference($lastAlertSentTimestamp)['minutes'];
                $minRequired   = Environment::var('monitoring_settings')['minsBetweenAlerts'];

                /**
                 * If there's more than 2 alerts, use minsAfterManyAlerts.
                 */
                if ($site->getAlertsCount() >= 2) $minRequired = Environment::var('monitoring_settings')['minsAfterManyAlerts'];

                if ($minsPassed < $minRequired) {
                    echo 'Not enough time passed since last alert ('.$minsPassed.' passed, '.$minRequired.' required)' . PHP_EOL;
                    return;
                } else {
                    echo 'Enough time passed since last alert ('.$minsPassed.' passed, '.$minRequired.' required)' . PHP_EOL;
                }
                break;
            case 'workingAgain':
                $incidentID = (new Site($this->getSiteID()))->getIncidentID();
                if (empty($incidentID)) break;

                $incident = new Incident($incidentID);
                $incident->resolve();
                break;
        }


        foreach ($this->assignedUsers as $user) {
            $this->sendToUser(intval($user));
            sleep(3);
        }

        /**
         * Clear issue data only after sending recovery alerts.
         */
        if ($this->alertType == 'workingAgain') SiteMonitoringDB::clearSiteData($this->getSiteID());
    }

    protected function getAssignedUsers() {
        $this->assignedUsers = SiteMonitoringDB::getSiteOwners($this->getSiteID());
        echo PHP_EOL . 'Assigned users: ' . PHP_EOL;
        print_r($this->assignedUsers);
    }

    protected function sendToUser(int $userID) {
        echo 'Send alert to user: ' . $userID . PHP_EOL;
        /**
         * Apply user's settings.
         * We create new Text for each user because of different languages.
         */
        UserSettings::setUserID($userID);
        $this->text = new Text();

        match($this->getAlertType()) {
            'firstAlert'    => $this->sendFirstAlert($userID),
            'anotherAlert'  => $this->sendAnotherAlert($userID),
            'workingAgain'  => $this->sendWorkingAgainAlert($userID),
        };
    }

    protected function sendFirstAlert(int $userID) {
        $text   = $this->text;
        $site   = new Site($this->getSiteID());
        $report = new Report();
        $reason = $this->getReason();


        $report->setTitle($text->sprintf('Сайт %s не работает! Проверьте его.', $site->getURL()));

        switch($reason['type']) {
            case 'wrongCode':
                $report->addBlock($text->sprintf('Сайт ответил кодом %s.', $reason['code']));
                $report->addBlock('▶️ ' . HttpDescription::getCodeDescription($reason['code'], $text->getCurrentLanguage()));
                break;
            case 'timeout':
                $report->addBlock($text->sprintf('Ответа сайта пришлось ждать %s секунд.', $reason['timeout']));
                break;
        }

        $data = array(
            'chat_id'       => PackDB::getChatIDByUserID($userID),
            'text'          => $report->getReport(),
            'parse_mode'    => 'HTML',
            'reply_markup'  => $this->getAlertKeyboard(),
            'disable_web_page_preview' => true,
        );

        $response = Request::sendMessage($data);

        echo PHP_EOL;
        print_r($response);
        echo PHP_EOL;
        print_r($data);
    }


    protected function sendAnotherAlert(int $userID) {
        $text   = $this->text;
        $site   = new Site($this->getSiteID());
        $report = new Report();
        $reason = $this->getReason();

        /**
         * Save last alert timestamp.
         */
        SiteMonitoringDB::updateSiteData(array(
            'lastAlertSent' => time(),
            'alertCount'    => $site->getAlertsCount() + 1,
        ), $this->getSiteID());

        $report->setTitle($text->sprintf('Сайт %s все еще не работает, он перестал работать %s.', $site->getURL(), $this->getRelativeTime($site->getFirstAlertTimestamp())));

        switch($reason['type']) {
            case 'wrongCode':
                $report->addBlock($text->sprintf('Сайт ответил кодом %s.', $reason['code']));
                $report->addBlock('▶️ ' . HttpDescription::getCodeDescription($reason['code'], $text->getCurrentLanguage()));
                break;
            case 'timeout':
                $report->addBlock($text->sprintf('Ответа сайта пришлось ждать %s секунд.', $reason['timeout']));
                break;
        }

        $data = array(
            'chat_id'       => PackDB::getChatIDByUserID($userID),
            'text'          => $report->getReport(),
            'parse_mode'    => 'HTML',
            'reply_markup'  => $this->getAlertKeyboard(),
            'disable_web_page_preview' => true,
        );

        $response = Request::sendMessage($data);

        echo PHP_EOL;
        print_r($response);
        echo PHP_EOL;
        print_r($data);

    }

    protected function sendWorkingAgainAlert(int $userID) {
        $text   = $this->text;
        $site   = new Site($this->getSiteID());
        $report = new Report();


        $report->setTitle($text->sprintf('Сайт %s заработал! Первая проблема была зафиксирована %s.', $site->getURL(), $this->getRelativeTime($site->getFirstAlertTimestamp())));

        $data = array(
            'chat_id'       => PackDB::getChatIDByUserID($userID),
            'text'          => $report->getReport(),
            'parse_mode'    => 'HTML',
            'reply_markup'  => $this->getAlertKeyboard(),
            'disable_web_page_preview' => true,
        );

        $response = Request::sendMessage($data);

        echo PHP_EOL;
        print_r($response);
        echo PHP_EOL;
        print_r($data);

    }

    protected function getAlertKeyboard(): InlineKeyboard {
        $text = $this->text;
        $keyboard = new InlineKeyboard(array(
            array(
                'text'          => '❌ ' . $text->e('Перестать отслеживать'),
                'callback_data' => 'Site_deleteSite_' . $this->getSiteID(),
            ),
            array(
                'text'          => '🏠 ' . $text->e('Главное меню'),
                'callback_data' => 'DomainChecks_backToMainMenu',
            )
        ));

        return $keyboard;
    }
}
