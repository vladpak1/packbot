<?php

namespace PackBot;

use Spatie\Dns\Dns as DnsSpatie;
use Spatie\Dns\Support\Domain;

class Dns {

    protected string $domain;

    protected bool $isExecuted = false;

    protected array $records = array();

    /**
     * This class is a wrapper for the spatie/dns package.
     * 
     * @param string $domain The domain to lookup.
     * @throws DnsException
     */
    public function __construct(string $domain) {
        $this->domain = Url::getDomain($domain);
    }

    public function execute(): self {
        if ($this->isExecuted) throw new DnsException('Dns lookup already executed.');
        $this->realExecute();
        return $this;
    }

    /**
     * @return DnsRecords[] Array of DnsRecords.
     */
    public function getRecords(): array {
        if (!$this->isExecuted) throw new DnsException('Dns lookup not executed.');
        return $this->records;
    }

    protected function realExecute() {
        try {
            $this->isExecuted = true;

            $dns             = new DnsSpatie();
            $records         = $dns->getRecords(new Domain($this->domain));
            $recordsPrepared = array();

            foreach ($records as $record) {
                /**
                 * @var \Spatie\Dns\Records\Record $record
                 */
                $recordArray = $record->toArray();

                $recordsPrepared[] = new DnsRecord($recordArray);

            }
            $this->records = $recordsPrepared;
        } catch (\Throwable $e) {
            throw new DnsException($e->getMessage());
        }
    }
}
