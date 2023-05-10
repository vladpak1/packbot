<?php

namespace PackBot;

use Iodev\Whois\Exceptions\ConnectionException;
use Iodev\Whois\Exceptions\ServerMismatchException;
use Iodev\Whois\Exceptions\WhoisException;
use Iodev\Whois\Factory;
use Iodev\Whois\Modules\Tld\TldInfo;

class WhoisTool implements ToolInterface
{
    use TimeTrait;

    protected array $domains = [];

    protected array $whoisInfo = [];

    protected Text $text;

    protected array $toolSettings;

    /**
     * @method array getDomainsAge() Get domains age. Returns array with keys: createdDateTimestamp, relativeTimeString, creationDateString, expirationDateString.
     * @method array getWhois()      Get whois info.
     */
    public function __construct(array|string $domains)
    {
        if (is_string($domains)) {
            $domains = [$domains];
        }

        $this->text = new Text();

        $this->toolSettings = Environment::var('tools_settings')['WhoisTool'];

        if (false === $this->toolSettings['enabled']) {
            throw new ToolException('Whois tool is temporarily disabled.');
        }

        $this->domains = $domains;
        $this->getWhoisInfo();
    }

    /**
     * Get the result of the whois check.
     * @example array('example.com' => TldInfo)
     * @see TldInfo for more details.
     */
    public function getResult(): array
    {
        return $this->whoisInfo;
    }

    public function getDomainsAge(): array
    {
        $ages = [];

        foreach ($this->whoisInfo as $domain => $info) {

            if ('' == $info->creationDate || '' == $info->expirationDate) {
                throw new ToolException($this->text->sprintf('Не удалось получить дату создания или дату окончания действия для домена %s', $domain));
            }

            $ages[$domain] = [
                'createdDateTimestamp' => $info->creationDate,
                'relativeTimeString'   => $this->getShortRelativeTime($info->creationDate),
                'creationDateString'   => $this->getReadableTime($info->creationDate),
                'expirationDateString' => $this->getReadableTime($info->expirationDate),
            ];

        }

        return $ages;
    }

    public function getWhoisText(string $domain): string
    {
        return $this->removeCopyrightText($this->getResult()[$domain]->getResponse()->getText());
    }

    protected function removeCopyrightText(string $text): string
    {
        $pos = strpos($text, 'For more information');

        if (false !== $pos) {
            $text = substr($text, 0, $pos);
        }

        return $text;
    }

    protected function getWhoisInfo()
    {
        sleep(5);

        foreach ($this->domains as $domain) {
            sleep(10);
            $this->whoisInfo[$domain] = $this->tryToGetWhois($domain);
        }
    }

    /**
     * Trying to get whois info for domain.
     * @param  string        $domain Domain name.
     * @throws ToolException If cannot get whois info.
     * @return TldInfo       Whois info.
     */
    protected function tryToGetWhois(string $domain): TldInfo
    {
        try {
            $whois = Factory::get()->createWhois();
            $info  = $whois->loadDomainInfo($domain);

            if (!$info) {
                throw new ToolException('Cannot get whois info for domain ' . $domain);
            }

            return $info;

        } catch (ConnectionException $e) {

            error_log("Cannot connect to whois server for domain {$domain}: {$e->getMessage()}");

            throw new ToolException($this->text->sprintf('Не удалось подключиться к whois серверу для домена %s', $domain));

        } catch (ServerMismatchException $e) {

            error_log("Whois server for domain {$domain} not found: {$e->getMessage()}");

            throw new ToolException($this->text->sprintf('Whois сервер для домена %s не найден', $domain));

        } catch (WhoisException $e) {

            error_log("Whois server for domain {$domain} not found: {$e->getMessage()}");

            throw new ToolException($this->text->sprintf('Whois сервер для домена %s не найден', $domain));

        }
    }
}
