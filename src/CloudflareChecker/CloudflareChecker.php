<?php

namespace PackBot;

class CloudflareChecker {

    protected array $rangesLinks = array(
        'https://www.cloudflare.com/ips-v4',
    );

    protected array $ranges = array();

    /**
     * This class is used for determining if the IP-address is part of Cloudflare network.
     * 
     * Cloudflare's known addresses are used to determine if the IP-address is part of Cloudflare network.
     * Please note that ipv6 is not supported.
     * 
     * @throws CloudflareCheckerException
     */
    public function __construct() {
        $this->prepareRanges();
    }

    /**
     * Determines if the IP-address is part of Cloudflare network.
     * 
     * @throws CloudflareCheckerException
     * @param string $ip IP-address to check. Supports both IPv4 and IPv6.
     */
    public function isCloudflare(string $ip): bool {
        if (!$this->isValidIP($ip)) throw new CloudflareCheckerException('Invalid IP-address.');
        
        foreach ($this->ranges as $range) {
            if ($this->isIPInRange($ip, $range)) return true;
        }

        return false;
    }

    /**
     * Supports both IPv4 and IPv6.
     */
    protected function isValidIP(string $ip): bool {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    protected function prepareRanges() {
        if (!empty($this->ranges)) throw new CloudflareCheckerException('Cloudflare ranges already prepared.');

        foreach ($this->rangesLinks as $link) {
            $curl = new Curl($link);

            $response = $curl->setTimeout(10)->execute()->getResponse();

            if (!$response->isOK()) {
                throw new CloudflareCheckerException(sprintf('Error while getting Cloudflare ranges %s. Error: %s', $link, $response->getCurlError()));
            }

            $this->ranges = array_merge($this->ranges, explode("\n", $response->getBody()));
        }
    }

    protected function isIPInRange(string $ip, string $range): bool {
        list($subnet, $mask) = explode('/', $range);

        $ipType = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? 'ipv4' : 'ipv6';

        switch($ipType) {
            case 'ipv4':
                $ip = ip2long($ip);
                $subnet = ip2long($subnet);
                $mask = -1 << (32 - $mask);
                return ($ip & $mask) === $subnet;
            case 'ipv6':
                return false;
            default:
                return false;
        }
    }
}
