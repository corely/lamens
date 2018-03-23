<?php
/**
 * Created by PhpStorm.
 * User: liuwenbin
 * Date: 2018/3/22
 * Time: 下午7:24
 */

namespace Lamens\Http;

use Illuminate\Http\Request as IlluminateRequest;

class Request {
    /**
     * @var \swoole_http_request
     */
    private $request;

    /**
     * Request constructor.
     *
     * @param \swoole_http_request $request
     */
    public function __construct($request) {
        $this->request = $request;
    }

    /**
     * Initialize the global variable _SERVER.
     */
    private function initGlobalServer() {
        $server = isset($request->server) ? $this->request->server : [];
        $header = isset($request->header) ? $this->request->header : [];

        foreach ($header as $key => $value) {
            $key = str_replace('-', '_', $key);
            $server['http_' . $key] = $value;
        }
        // Fix client real-ip
        if (isset($request->header['x-real-ip'])) {
            $server['REMOTE_ADDR'] = (string)$this->request->header['x-real-ip'];
        }
        // Fix client real-port
        if (isset($request->header['x-real-port'])) {
            $server['REMOTE_PORT'] = (int)$this->request->header['x-real-port'];
        }
        $_SERVER = array_merge($_SERVER, array_change_key_case($server, CASE_UPPER));

        // Fix argv & argc
        if (!isset($_SERVER['argv'])) {
            $_SERVER['argv'] = isset($GLOBALS['argv']) ? $GLOBALS['argv'] : [];
            $_SERVER['argc'] = isset($GLOBALS['argc']) ? $GLOBALS['argc'] : 0;
        }
    }

    /**
     * Initialize the global variable _REQUEST.
     */
    private function initGlobalRequest() {
        $_REQUEST = [];
        $requests = ['C' => $_COOKIE, 'G' => $_GET, 'P' => $_POST];
        $requestOrder = ini_get('request_order') ?: ini_get('variables_order');
        $requestOrder = preg_replace('#[^CGP]#', '', strtoupper($requestOrder)) ?: 'GP';
        foreach (str_split($requestOrder) as $order) {
            $_REQUEST = array_merge($_REQUEST, $requests[$order]);
        }
    }

    /**
     * Initialize the global variables.
     */
    private function initGlobals() {
        $_GET = isset($request->get) ? $this->request->get : [];
        $_POST = isset($request->post) ? $this->request->post : [];
        $_COOKIE = isset($request->cookie) ? $this->request->cookie : [];
        $_FILES = isset($request->files) ? $this->request->files : [];

        $this->initGlobalServer();
        $this->initGlobalRequest();
    }

    /**
     * Create a new Illuminate HTTP request from server variables.
     *
     * @return \Illuminate\Http\Request
     */
    private function capture() {
        $request = IlluminateRequest::capture();
        $reflection = new \ReflectionObject($request);
        $content = $reflection->getProperty('content');
        $content->setAccessible(true);
        $content->setValue($request, $this->request->rawContent());

        if (0 === strpos($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH'))
        ) {
            parse_str($request->getContent(), $data);
            $request->request = new ParameterBag($data);
        }

        return $request;
    }

    /**
     * Convert Swoole HTTP Request to Illuminate HTTP request.
     *
     * @return \Illuminate\Http\Request
     */
    public function convert() {
        $this->initGlobals();
        $request = $this->capture();
        return $request;
    }
}