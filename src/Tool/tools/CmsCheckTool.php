<?php

namespace PackBot;

use DetectCMS\DetectCMS;
use Throwable;

class CmsCheckTool implements ToolInterface {

    protected array $domains = array();

    protected Text $text;

    protected array $cmsInfo = array();

    protected array $toolSettings;

    public function __construct(array|string $domains) {
        if (is_string($domains)) $domains = array($domains);

        $this->text = new Text();

        $this->toolSettings = Environment::var('tools_settings')['CmsCheckTool'];
        if ($this->toolSettings['enabled'] === false) throw new ToolException('Whois tool is temporarily disabled.');


        $this->domains = $domains;
        $this->getCMS();
    }

    /**
     * Get the result of the cms check.
     * @example array('example.com' => 'wordpress')
     */
    public function getResult(): array {
        return $this->cmsInfo;
    }

    protected function getCMS() {
        sleep(5);
        foreach ($this->domains as $domain) {
            sleep(10);
            $this->cmsInfo[$domain] = array(
                'cms' => $this->realGetCMS($domain),
            );
        }
    }

    /**
     * Shitty code. I know. Maybe I'll fix it later.
     * @todo
     */
    protected function realGetCMS(string $domain) {
        try {
            @$cms = new DetectCMS($domain);

            $result = $cms->getResult();
    
            if ($result == '' || $result == false) {
                sleep(5);
                /**
                 * Give it another try.
                 */
                @$cms   = new DetectCMS($domain);
                $result = $cms->getResult();
    
                if ($result == '' || $result == false) {
                    /**
                     * Try to add https://.
                     */
                    sleep(2);
                    if (str_contains($domain, 'https://') === false) {
                        $domain = 'https://' . $domain;
                        @$cms   = new DetectCMS($domain);
                        $result = $cms->getResult();
    
                        if ($result == '' || $result == false) {
                            $result = 'unknown';
                        }
    
                    } else {
                        $result = 'unknown';
                    }
                }
            }
            return $result;
        } catch (Throwable $e) {
            error_log('CmsCheckTool for domain '.$domain.': ' . $e->getMessage());
            throw new ToolException('Cannot check CMS for domain ' . $domain);
        }
    }

}
