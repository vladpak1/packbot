<?php

namespace PackBot;

use RemotelyLiving\PHPDNS\Resolvers\CloudFlare;
use RemotelyLiving\PHPDNS\Resolvers\GoogleDNS;

class Dns {

    protected string $domain;

    protected bool $isExecuted = false;

    public function __construct(string $domain) {
        $this->domain = $domain;
    }

    public function execute(): self {
        if ($this->isExecuted) throw new DnsException('Dns lookup already executed.');
        $this->realExecute();
        return $this;
    }

    protected function realExecute() {
        $this->isExecuted = true;

        $cloudFlareResolver = new CloudFlare();

        $records = $cloudFlareResolver->getRecords($this->domain);
    }


}