<?php
/**
 * Bot Config Sample.
 */
return [
    /**
     * Telegram Bot API key.
     */
    'bot_api_key' => 'api_key',
    /**
     * Key for doing cronjobs.
     */
    'cronjob_key' => 'random_key_to_run_cronjob',
    /**
     * Telegram Bot username.
     */
    'bot_username' => 'user_name_without_@',
    /**
     * Webhook URL.
     */
    'hook_url' => 'full_link_to_hook.php',
    /**
     * MySQL connection settings.
     */
    'db_host'     => 'localhost',
    'db_user'     => 'user',
    'db_password' => 'pass',
    'db_name'     => 'name',
    /**
     * Do we need to enable global limiter?
     * If enabled, the telegram will try to limit the number of requests to 1 per second.
     */
    'enable_global_limiter' => true,
    /**
     * Do we need to enable logging?
     * If enabled, the bot will log all Telegram Updates and API Requests
     * to the root folder.
     */
    'logging' => false,
    /**
     * Specify admins IDs to enable some technical commands in the chat.
     */
    'admins' => [
        //array of integers
    ],
    /**
     * Specify the users ID that can't access the bot.
     */
    'banList' => [
        //array of integers
    ],
    /**
     * Screen settings.
     */
    'screen_settings' => [
        'useTempMessages' => false,
    ],
    'tools_settings' => [
        'WhoisTool' => [
            'enabled'          => true,
            'maxDomainsAtOnce' => 10,
        ],
        'CmsCheckTool' => [
            'enabled' => true,
        ],
        'ServerResponseCheckTool' => [
            'enabled' => true,
        ],
        'PageSpeedTool' => [
            'enabled' => true,
            'apiKey'  => 'api_key', //leave it empty to use PageSpeed without api key (less requests per day)
        ],
        'RedirectTraceTool' => [
            'enabled'          => true,
            'maxRedirects'     => 30,
            'maxDomainsAtOnce' => 10,
        ],
        'IndexPossibilityCheckTool' => [
            'enabled'          => true,
            'maxDomainsAtOnce' => 10,
        ],
        'SitemapParserTool' => [
            'enabled'             => false,
            'maxSitemapsAtOnce'   => 150,
            'timeLimit'           => 600,
            'sitemapCurlWaitTime' => 50,
        ],
        'UrlTrimmerTool' => [
            'maxUrlsAtOnce' => 100000,
        ],
        'DnsTool' => [
            'enabled'          => true,
            'maxDomainsAtOnce' => 10,
        ],
    ],
    'monitoring_settings' => [
        'maxSitesPerUser'     => 10,
        'minsBetweenAlerts'   => 30,
        'minsAfterManyAlerts' => 320,
        'siteChecker'         => [
            'downStateConditions' => [
                'timeout' => 5,
            ],
            'siteCheckInterval'     => 10, //mins
            'downSiteCheckInterval' => 3, //mins
            'noCacheUrl'            => true, //add random param to url to prevent caching
            'disabled'              => false, //disable site checks
        ],
        'cleaner' => [ //cleaner settings
            //3 days
            'incidentDurationToRemoveSite' => 4320, //Incident time in minutes after which the site will be removed
        ],
    ],
];
