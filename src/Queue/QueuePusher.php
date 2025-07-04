<?php

namespace PackBot;

use Illuminate\Queue\Capsule\Manager as QueueCapsule;
use Illuminate\Database\Capsule\Manager as DB;

class QueuePusher
{
    public function __construct()
    {
        require_once __DIR__ . '/../../queue.php';
    }

    public function push(\Illuminate\Contracts\Queue\ShouldQueue $job): mixed
    {
        return QueueCapsule::connection('default')->push($job);
    }

    public function pushCheck(int $siteID): mixed
    {
        $inserted = DB::table('pending_site_checks')
            ->insertOrIgnore(['site_id' => $siteID]);

        if ($inserted) {

            return QueueCapsule::connection('default')->push(new CheckSiteJob($siteID));
        }

        error_log("CheckSiteJob for site {$siteID} is already queued.");

        return null;
    }
}
