<?php

namespace PackBot;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Database\Capsule\Manager as DB;
use Throwable;

class CheckSiteJob implements ShouldQueue
{
    use InteractsWithQueue;

    public int $timeout = 60;

    public int $tries = 1;

    protected int $siteID;

    public function __construct(int $siteID)
    {
        $this->siteID = $siteID;
    }

    public function handle(): void
    {

        try {
            new Worker(); // Initialize the environment
            $siteChecker = new SiteChecker(new Site($this->siteID));
            $siteChecker->process();

            $alerts = $siteChecker->getAlerts();

            foreach ($alerts as $alert) {
                /**
                 * @var Alert $alert
                 */
                $alert->send();
            }

            DB::table('pending_site_checks')
                ->where('site_id', $this->siteID)
                ->delete();
        } catch (Throwable $exception) {
            $this->fail($exception);
        }
    }

    public function failed(Throwable $exception): void
    {
        $this->logError($exception);

        DB::table('pending_site_checks')
            ->where('site_id', $this->siteID)
            ->delete();
    }

    protected function logError(Throwable $exception): void
    {
        $logFile = ROOT_DIR . '/temp/error.log';
        $message = sprintf(
            "[%s] CheckSiteJob(%d) error: %s in %s:%d\nStack trace:\n%s\n\n",
            date('c'),
            $this->siteID,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);
    }
}
