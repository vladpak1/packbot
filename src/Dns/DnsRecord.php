<?php

namespace PackBot;

class DnsRecord extends Entity
{
    protected array $data;

    protected ?bool $isCloudflare = null;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->cloudflareCheck();
    }

    public function __toString(): string
    {
        return $this->jsonSerialize();
    }

    public function jsonSerialize(): string
    {
        return json_encode($this->data);
    }

    public function getRawData(): object
    {
        return (object) $this->data;
    }

    public function getHost(): ?string
    {
        return $this->data['host'] ?? null;
    }

    public function getTtl(): ?int
    {
        return $this->data['ttl'] ?? null;
    }

    public function getType(): ?string
    {
        return $this->data['type'] ?? null;
    }

    public function getPriority(): ?int
    {
        return $this->data['priority'] ?? null;
    }

    public function getIP(): ?string
    {
        //ip could be in $this->data['ip'] or in $this->data['ipv6']
        return $this->data['ip'] ?? $this->data['ipv6'] ?? null;
    }

    public function getTarget(): ?string
    {
        return $this->data['target'] ?? null;
    }

    public function getText(): ?string
    {
        return $this->data['txt'] ?? null;
    }

    /**
     * @return bool|null Returns null if IP-address is not set or if there was an error while checking.
     */
    public function isCloudflare(): ?bool
    {
        return $this->isCloudflare;
    }

    protected function cloudflareCheck(): void
    {
        if (empty($this->getIP())) {
            return;
        }

        try {

            $cloudflareChecker  = new CloudflareChecker();
            $this->isCloudflare = $cloudflareChecker->isCloudflare($this->getIP());

        } catch (CloudflareCheckerException $e) {
            error_log($e->getMessage());

            return;
        }
    }
}
