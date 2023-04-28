<?php

namespace PackBot;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use Spatie\GuzzleRedirectHistoryMiddleware\RedirectHistory;
use Spatie\GuzzleRedirectHistoryMiddleware\RedirectHistoryMiddleware;
use Throwable;

class RedirectTracer {


    protected string $url;

    protected array $redirectHistory = array();


    public function __construct(string $url) {
        $this->url = $url;
        $this->trace();
    }

    public function getRedirectHistory(): array {
        return $this->redirectHistory;
    }

    protected function trace() {

        try {
            $redirectHistory = new RedirectHistory();

            $stack = HandlerStack::create();
            $stack->push(RedirectHistoryMiddleware::make($redirectHistory));

            $client = new Client(array(
                'handler' => $stack,
            ));

            $response = $client->get($this->url);

            $this->redirectHistory = $redirectHistory->toArray();
        } catch (RequestException $e) {
            Debug::toConsole($e);
            throw new RedirectTracerException(sprintf('Request resulted in %d response code.', $e->getCode()));
        } catch (Throwable $e) {
            error_log('RedirectTracerException: ' . $e->getMessage() . ' (' . $this->url . ')');
            throw new RedirectTracerException(Format::prepDisplay($e->getMessage()));
        }

    }
}
