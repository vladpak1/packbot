<?php

namespace PackBot;

use Throwable;

class CurlReport extends Report {

    protected CurlResponse $response;

    protected $curlReport = '';

    public function __construct(CurlResponse $response) {
        $this->response = $response;
        parent::__construct();
        $this->prepareCurlReport();
    }
        
    public function getReport(): string {
        return parent::getReport();
    }

    public function getReportImage(): string|bool {
        try {
            return $this->response->getPageSpeed()->getSummary()['screenshots']['final'];
        } catch (Throwable $e) {
            return false;
        }
    }

    protected function prepareCurlReport() {

        $this
            ->setTitle($this->sprintf('Ответ %s', $this->response->getEffectiveUrl()))
            ->addBlock($this->concatEOL(
            $this->sprintf('Код ответа: %s', $this->response->getCode()),
            $this->sprintf('Тип ответа: %s', $this->response->getContentType()),
            $this->sprintf('IP-адрес: %s', $this->response->getIP()),
            $this->sprintf('Ответ DNS: %s мс', $this->response->getDnsLookupTime()),
            $this->sprintf('Время ответа: %s мс', $this->response->getTotalTime()),
            $this->sprintf('Скорость загрузки: %s мб/c', $this->response->getSpeedDownload()),
        ));

            $this->prepareCommentsBlock();
    }

    protected function prepareCommentsBlock() {

        $this->addBlock($this->concatEOL(
            $this->sprintf('Код ответа %s:', $this->response->getCode()),
            HttpDescription::getCodeDescription($this->response->getCode(), $this->getCurrentLanguage())
        ));

    }

}