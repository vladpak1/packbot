<?php

namespace PackBot;


class SiteManager {

    protected int $userID;

    protected Text $text;

    /**
     * This class is responsible for managing sites in the monitoring system.
     * Immutable userID is required.
     */
    public function __construct(int $userID) {
        $this->userID = $userID;
        $this->text = new Text();
    }

    /**
     * Add site to monitoring and assign it to the user
     * 
     * @param string $domain
     * @return bool
     * @throws InvalidDomainException|SiteMonitoringException
     */
    public function addSite(string $domain): bool {
        if (!Url::isValid($domain)) throw new InvalidDomainException('Invalid domain: ' . $domain);

        try {
            $effectiveUrl = Url::getEffectiveUrl($domain);
        } catch (CurlException $e) {
            throw new SiteMonitoringException($this->text->sprintf('Не удалось выполнить запрос к сайту %s. Вы можете добавлять в мониторинг только существующие и работающие сайты.', $domain));
        }

        /**
         * After the release, it became known that for some reason, in some situations,
         * an address with a port can be returned.
         * Throw an exception when such situations are encountered.
         */
        if (Url::hasPort($effectiveUrl)) throw new SiteMonitoringException($this->text->sprintf('Эффективный адрес (%s) сайта содержит порт, что недопустимо для этого бота. Попробуйте еще раз.', $effectiveUrl));

        /**
         * Check if user has exceeded the limit of sites
         */
        if (SiteMonitoringDB::getUsersSitesCount($this->userID) >= Environment::var('monitoring_settings')['maxSitesPerUser']) throw new SiteMonitoringException($this->text->sprintf('Вы можете добавить не более %d сайтов в мониторинг.', Environment::var('monitoring_settings')['maxSitesPerUser']));

        if (SiteMonitoringDB::isSiteAssignedToUser($effectiveUrl, $this->userID)) throw new SiteMonitoringException($this->text->e('Сайт уже добавлен в мониторинг.'));

        SiteMonitoringDB::addSite($effectiveUrl);
        return SiteMonitoringDB::assignOwnerToSite($effectiveUrl, $this->userID);
    }


    public function getSites(): array {
        $sites = SiteMonitoringDB::getUsersSitesIDs($this->userID);
        $objects = array();
        foreach ($sites as $id) {
            $objects[] = new Site($id);
        }

        return $objects;
    }
    
    public function unassignOwnerFromSite(int $siteID): bool {
        return SiteMonitoringDB::unassignOwnerFromSite($siteID, $this->userID);
    }
}
