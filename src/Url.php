<?php
namespace PackBot;

class Url {

    /**
     * Note that this method considers a valid url even if it doesn't provide a protocol.
     */
    public static function isValid(string $url): bool {
        $url = trim($url);
        $url = self::maybeAddProtocol($url);

        if (str_contains($url, ' ') || !str_contains($url, '.')) {
            return false;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Gets only the domain from url. Deletes www, /, tokens and other things.
     */
    public static function getDomain(string $url): string {
        $url = self::maybeAddProtocol($url);
        $parsed = parse_url($url);
        $domain = preg_replace('/^www\./', '', $parsed['host']);
        return $domain;
    }

    public static function removeWWW(string $url): string {
        $url = self::maybeAddProtocol($url);
        $parsed = parse_url($url);
        $domain = preg_replace('/^www\./', '', $parsed['host']);
        $parsed['host'] = $domain;
        return self::unparseUrl($parsed);
    }

    public static function removeSubdomain(string $url): string {
        $url = self::maybeAddProtocol($url);
        $parsed = parse_url($url);
        $host_parts = explode('.', $parsed['host']);
        $host_parts = array_slice($host_parts, -2, 2);
        $parsed['host'] = implode('.', $host_parts);
        return self::unparseUrl($parsed);
    }

    /**
     * Get effective url using Curl class.
     * @throws CurlException
     */
    public static function getEffectiveUrl(string $url): string {
        $curl = new Curl($url);

        $response = $curl->setTimeout(30)->execute()->getResponse();

        if ($response->getCode() === 200) {
            return $response->getEffectiveUrl();
        } else {
            throw new CurlException('Curl error: ' . $response->getCode());
        }
    }

    /**
     * Check if there's a port represented in the url.
     */
    public static function hasPort(string $url): bool {
        $url    = self::maybeAddProtocol($url);
        $parsed = parse_url($url);
        return isset($parsed['port']);
    }

    /**
     * Helper function to reconstruct URL from parsed array.
     */
    private static function unparseUrl(array $parsed): string {
        $scheme = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '';
        $user = isset($parsed['user']) ? $parsed['user'] : '';
        $pass = isset($parsed['pass']) ? ':' . $parsed['pass']  : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $host = isset($parsed['host']) ? $parsed['host'] : '';
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $path = isset($parsed['path']) ? $parsed['path'] : '';
        $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
        $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    private static function maybeAddProtocol(string $url): string {
        $url = trim($url);
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'http://' . $url;
        }
        return $url;
    }
}

