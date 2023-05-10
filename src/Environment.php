<?php

namespace PackBot;

/**
 * Class for working with environment variables.
 */
class Environment
{
    /**
     * Getting environment variable.
     * @throws EnvironmentException
     * @return array|string|int|bool
     */
    public static function var(string $key)
    {
        if (defined('TESTSUITE')) {
            return self::varForTest($key);
        }
        self::isConfigOK();

        $envPath = Path::toRoot() . '/config.php';

        $env = require $envPath;

        if (!isset($env[$key])) {
            throw new EnvironmentException('Environment variable ' . $key . ' not found.');
        }

        return $env[$key];
    }

    /**
     * Getting all environment variables as an array.
     */
    public static function all(): array
    {
        self::isConfigOK();

        $envPath = Path::toRoot() . '/config.php';

        return require $envPath;
    }

    /**
     * Checks if environment variables list is OK.
     * @throws EnvironmentException
     */
    protected static function isConfigOK()
    {

        $configPath = Path::toRoot() . '/config.php';

        if (!file_exists($configPath)) {
            throw new EnvironmentException('Config file not found (checked path: ' . $configPath . '). It should be in the root directory of the project and be named config.php.');
        }

        if (!is_readable($configPath)) {
            throw new EnvironmentException('Config file is not readable. Please check the file permissions.');
        }

        if (!is_writable($configPath)) {
            throw new EnvironmentException('Config file is not writable. Please check the file permissions.');
        }
    }

    protected static function varForTest(string $key)
    {
        $env = require Path::toRoot() . '/tests/config.test.php';

        if (!isset($env[$key])) {
            throw new EnvironmentException('Environment variable ' . $key . ' not found.');
        }

        return $env[$key];
    }
}
