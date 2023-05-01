<?php


namespace PackBot;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Cleaner {

    protected array $cleanerSettings;

    /**
     * This class is used for this tasks:
     * 1. Delete sites that are not associated with any user.
     * 2. Remove sites that do not work for a long time.
     * 3. Deleting temporary files
     */
    public function __construct() {
        $this->cleanerSettings = Environment::var('monitoring_settings')['cleaner'];
        echo 'Cleaner created' . PHP_EOL;
    }

    public function executeAll() {
        $this->deleteSitesWithNoOwners();
        $this->deleteSitesWithLongIncident();
        $this->deleteTempFiles();
        $this->deleteOldIncidents();
    }

    public function deleteSitesWithNoOwners() {
        echo 'Delete sites with no owners' . PHP_EOL;
        $removed = SiteMonitoringDB::removeSitesWithoutOwners();
        echo 'Removed: ' . $removed . PHP_EOL;
    }

    public function deleteSitesWithLongIncident() {
        $sites = SiteMonitoringDB::getSitesIDs();
        echo 'Delete sites with long incidents' . PHP_EOL;
        $removed = 0;

        foreach ($sites as $siteID) {
            if (IncidentsDB::isThereActiveIncidentsForSite($siteID)) {
                echo 'There is active incidents for site ' . $siteID . PHP_EOL;

                $lastIncident = IncidentsDB::getLastIncident($siteID);
                
                $incident = new Incident($lastIncident['id']);

                $durationToDeleteInSeconds = $this->cleanerSettings['incidentDurationToRemoveSite'] * 60;

                $time = new Time();

                $duration = $incident->getDuration();

                echo 'Duration: ' . $duration . PHP_EOL;

                if ($duration > $durationToDeleteInSeconds) {
                    echo 'Duration is more than ' . $durationToDeleteInSeconds . ' seconds' . PHP_EOL;
                    echo 'Remove site: ' . $siteID . PHP_EOL;

                    /**
                     * Send notification to users assigned to this site.
                     */
                    $site = new Site($siteID);

                    echo 'Send message to owners' . PHP_EOL;
                    $messagesSent = $site->sendMessageToOwners(sprintf(
                        'Сайт %s не работает уже очень давно, поэтому он был удален из мониторинга.',
                        $site->getURL(),
                    ));
                    echo 'Messages sent: ' . $messagesSent . PHP_EOL;

                    SiteMonitoringDB::removeSite($siteID);


                    $removed++;
                } else {
                    echo 'Duration is less than ' . $durationToDeleteInSeconds . ' seconds (in config.php)' . PHP_EOL;
                }
            }
        }

        echo 'Removed: ' . $removed . PHP_EOL;
    }

    public function deleteTempFiles() {
        $dir = Path::toTemp();
        echo 'Delete temp files in ' . $dir . PHP_EOL;
        $files = scandir($dir);

        $removed = 0;

        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it,
                RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                echo 'Remove dir: ' . $file->getRealPath() . PHP_EOL;
                rmdir($file->getRealPath());
                $removed++;
            } else {
                echo 'Remove file: ' . $file->getRealPath() . PHP_EOL;
                unlink($file->getRealPath());
                $removed++;
            }
        }

        echo 'Removed: ' . $removed . PHP_EOL;
    }

    public function deleteOldIncidents() {
        echo 'Delete old incidents' . PHP_EOL;
        $removed = IncidentsDB::removeOldIncidents();
        echo 'Removed: ' . $removed . PHP_EOL;
    }


}
