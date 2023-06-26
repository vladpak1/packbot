<?php
/**
 * This config will be used during tests.
 */
return [
    /**
     * Telegram Bot API key.
     */
    'bot_api_key' => '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11',
    /**
     * Key for doing cronjobs.
     */
    'cronjob_key' => 'sdfsdf212',
    /**
     * Telegram Bot username.
     */
    'bot_username' => 'testbot',
    /**
     * Webhook URL.
     */
    'hook_url' => 'https://example.com/hook.php',
    /**
     * MySQL connection settings.
     */
    'db_host'     => '127.0.0.1',
    'db_user'     => 'root',
    'db_password' => 'password',
    'db_name'     => 'packbottest',
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
        285177721,
    ],
    /**
     * Specify the users ID that can't access the bot.
     */
    'banList' => [
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
            'apiKey'  => 'apiKey',
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
        'maxSitesPerUser'     => 30,
        'minsBetweenAlerts'   => 1,
        'minsAfterManyAlerts' => 2,
        'siteChecker'         => [
            'downStateConditions' => [
                'timeout' => 5,
            ],
            'siteCheckInterval'     => 1, //mins
            'downSiteCheckInterval' => 1, //mins
            'noCacheUrl'            => true, //add random param to url to prevent caching
            'disabled'              => false, //disable site checks
        ],
        'cleaner' => [ //cleaner settings
            //3 days
            'incidentDurationToRemoveSite' => 4320, //Incident time in minutes after which the site will be removed
        ],
    ],
];
