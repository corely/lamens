<?php
/**
 * Created by PhpStorm.
 * User: liuwenbin
 * Date: 2018/3/22
 * Time: 下午7:24
 */

namespace Lamens\Http;

use Illuminate\Support\Facades\Log;

class Response {
    /*
     * @var \swoole_http_response
     */
    protected $response;

    /**
     * Response constructor.
     *
     * @param \swoole_http_response $response
     */
    public function __construct($response) {
        $this->response = $response;
    }

    /**
     * Set the status of response.
     *
     * @param \Illuminate\Http\Response $response
     */
    protected function setStatusCode($response) {
        $this->response->status($response->getStatusCode());
    }

    /**
     * Set the header of response.
     *
     * @param \Illuminate\Http\Response $response
     */
    protected function setHeaders($response) {
        foreach ($response->headers->allPreserveCase() as $name => $values) {
            foreach ($values as $value) {
                $this->response->header($name, $value);
            }
        }
    }

    /**
     * Set the cookie of response.
     *
     * @param \Illuminate\Http\Response $response
     */
    protected function setCookies($response) {
        foreach ($response->headers->getCookies() as $cookie) {
            $this->response->cookie(
                $cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(),
                $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }
    }

    /**
     * Send response to client.
     * @param \Illuminate\Http\Response|string $response
     */
    public function send($response) {
        $this->setStatusCode($response);
        $this->setHeaders($response);
        $this->setCookies($response);

        $this->response->end($response->getContent());
    }
}