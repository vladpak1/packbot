<?php

namespace PackBot;

use Longman\TelegramBot\Request;
use RobotsTxtParser\RobotsTxtParser;

class SitemapParser {

    /**
     * Link to sitemap.xml file.
     */
    protected array $sitemaps;

    protected bool $isExecuted = false;

    protected array $links = array();

    protected int $maxSitemapsAtOnce = 10;

    protected int $sitemapCurlWaitTime = 40; //seconds

    protected int $sitemapsIterated = 0;

    protected ?int $timeLimit = null; //seconds

    protected float $startTime;

    protected array|false $telegramMessageData = false;

    protected int $totalSitemaps = 0;

    protected Text $text;

    protected Time $time;

    /**
     * This class is used to parse sitemaps.
     * Supports nested sitemaps.
     * 
     * @param string $sitemap Direct link to sitemap.xml file.
     * @throws SitemapParserException
     */
    public function __construct(string $sitemap) {
        $this->sitemaps = array($sitemap);
        $this->text     = new Text();
        $this->time     = new Time();
    }

    /**
     * Executes sitemap parsing.
     */
    public function execute() {
        if ($this->isExecuted) throw new SitemapParserException('SitemapParser is already executed.');

        $this->isExecuted = true;
        $this->startTime  = microtime(true);
        $this->updateMessage('Инициализация...');


        /**
         * If this is not a direct link to sitemap.xml file, then we need to get sitemap.xml file.
         */
        if (!$this->endsWithXml($this->sitemaps[0])) {
            $this->updateMessage('Получение файла sitemap.xml из robots.txt...');

            $this->sitemaps = $this->getSitemapXmlFileAddress($this->sitemaps[0]);
        }


        $this->startIteratingThroughSitemaps();

        return $this;
    }

    /**
     * Sets max sitemaps at once.
     */
    public function setMaxSitemapsAtOnce(int $maxSitemapsAtOnce) {
        $this->maxSitemapsAtOnce = $maxSitemapsAtOnce;
        return $this;
    }

    /**
     * Sets the time limit in seconds for iterating sitemaps.
     */
    public function setTimeLimit(int $timeLimit) {
        $this->timeLimit = $timeLimit;
        return $this;
    }

    /**
     * Sets the time in seconds that curl request to the sitemap will wait for response.
     */
    public function setSitemapCurlWaitTime(int $sitemapCurlWaitTime) {
        $this->sitemapCurlWaitTime = $sitemapCurlWaitTime;
        return $this;
    }

    /**
     * Returns array of links from sitemap(s).
     */
    public function getLinks(): array {
        return $this->links;
    }

    public function setTelegramProgressMessage(array $data): self {
        $this->telegramMessageData = $data;
        return $this;
    }

    protected function endsWithXml(string $string): bool {
        $extension = '.xml';
        $extLength = strlen($extension);

        if (strlen($string) < $extLength) return false;

        return substr($string, -$extLength) === $extension;
    }

    protected function getSitemapXmlFileAddress(string $url): array {
        try {
            $this->updateMessage('Получение файла robots.txt...');

            $curl = new Curl(Url::getDomain($url) . '/robots.txt');

            $response = $curl
                            ->setTimeout(20)
                            ->execute()
                            ->getResponse();
            
            if (!$response->isOK()) throw new SitemapParserException('Failed to get robots.txt file.');

            $robotsTxt = $response->getBody();

            if (strlen($robotsTxt) == 0) throw new SitemapParserException('Robots.txt file is empty.');

            $parser = new RobotsTxtParser($robotsTxt);

            $sitemaps = $parser->getSitemaps();

            if (count($sitemaps) == 0) throw new SitemapParserException('Robots.txt file does not contain sitemap.xml file address.');
            
            return $sitemaps;
            
        } catch (\Throwable $e) {
            throw new SitemapParserException('Failed to get sitemap.xml file address from robots.txt file: ' . $e->getMessage());
        }
    }

    protected function updateMessage(string $currentAction) {
        if ($this->telegramMessageData === false) return;

        $message = $this->text->concatDoubleEOL(
            '<b>Выполняется парсинг... Это может занять очень много времени.</b>',
            $this->text->sprintf('❗️ Действие: %s', $this->text->e($currentAction)),
            $this->text->sprintf('Времени прошло: %s (лимит %s)', $this->time->secondsToHumanReadable(round(microtime(true) - $this->startTime)), $this->time->secondsToHumanReadable($this->timeLimit)),
            $this->text->sprintf('Обработано сайтмапов: %d/%d (лимит %d)', $this->sitemapsIterated, $this->totalSitemaps,$this->maxSitemapsAtOnce),
            $this->text->sprintf('Ссылок собрано: %d', count($this->links)),
        );

        

        Request::editMessageText(array(
            'chat_id' => $this->telegramMessageData['chat_id'],
            'message_id' => $this->telegramMessageData['message_id'],
            'text' => $message,
            'parse_mode' => 'HTML',
        ));

        sleep(2);
    }

    protected function getSitemapContent(string $sitemap) {
        try {

            $this->updateMessage($this->text->sprintf('Получение контента сайтмапа - %s', $sitemap));

            $curl = new Curl($sitemap);
            $response = $curl
                            ->setTimeout($this->sitemapCurlWaitTime)
                            ->execute()
                            ->getResponse();

            if (!$response->isOK()) throw new SitemapParserException($this->text->sprintf('Не удалось получить контент сайтмапа %s, код ответа %s', $sitemap, $response->getCode()));

            return $response->getBody();
        } catch (CurlException $e) {
            throw new SitemapParserException($this->text->sprintf('Не удалось получить контент сайтмапа %s, ошибка: %s', $sitemap, $e->getMessage()));
        }
    }

    protected function startIteratingThroughSitemaps() {
        foreach ($this->sitemaps as $sitemap) {
            $this->iterateSitemap($sitemap);
        }

    }

    protected function iterateSitemap(string $sitemapLink) {
        $this->checkTimeLimit();
        $xml = simplexml_load_string($this->getSitemapContent($sitemapLink));

        $this->updateMessage($this->text->sprintf('Обработка сайтмапа - %s', $sitemapLink));

        if ($xml === false) throw new SitemapParserException($this->text->sprintf('Не удалось обработать сайтмап %s.', $sitemapLink));

        if ($this->sitemapsIterated >= $this->maxSitemapsAtOnce) throw new SitemapParserException($this->text->sprintf('Достигнуто максимальное количество сайтмапов (%d).', $this->maxSitemapsAtOnce));

        if ($xml->getName() === 'sitemapindex') {
            //this sitemap contains sitemaps
            $childSitemaps = $xml->children();

            $this->totalSitemaps += count($childSitemaps);
            if ($this->totalSitemaps >= $this->maxSitemapsAtOnce) {
                throw new SitemapParserException($this->text->sprintf('Продолжение парсинга превысит лимит на количество сайтмапов, поэтому он был остановлен. (лимит - %d, сайтмапов - %d).', $this->maxSitemapsAtOnce, $this->totalSitemaps));
            }

            foreach ($childSitemaps as $childSitemap) {
                $this->iterateSitemap($childSitemap->loc);
            }
        } else {
            //regular sitemap
            $this->getLinksFromSitemap($xml);
        }
    }

    protected function getLinksFromSitemap(\SimpleXMLElement $xml) {
        $this->sitemapsIterated++;
        foreach($xml->children() as $child) {
            $this->links[] = (string)$child->loc;
        }
    }

    protected function checkTimeLimit() {
        if ($this->timeLimit === null) {
            return;
        }

        $elapsedTime = microtime(true) - $this->startTime;
        if ($elapsedTime > $this->timeLimit) {
            throw new SitemapParserException($this->text->sprintf('Превышен лимит времени (%s).', $this->time->secondsToHumanReadable($this->timeLimit)));
        }
    }
}
