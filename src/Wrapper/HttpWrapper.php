<?php
/**
 * Created by PhpStorm.
 * User: liuwenbin
 * Date: 2018/3/21
 * Time: 下午5:13
 */

namespace Lamens\Wrapper;

use Lamens\Http\Request;
use Lamens\Http\Response;
use Illuminate\Support\Facades\Facade;

class HttpWrapper extends Base {

    /**
     * HttpWrapper constructor.
     *
     * @param array $conf
     */
    public function __construct($conf) {
        parent::__construct($conf);
        $this->server = new \swoole_http_server($conf['host'], $conf['port']);
    }

    /**
     * {@inheritdoc}
     */
    public function onRequest($request, $response) {
        $request = (new Request($request))->convert();
        $illuminateResponse = $this->app->handle($request);
        (new Response($response))->send($illuminateResponse);
    }

    /**
     * {@inheritdoc}
     */
    protected function bindEvent() {
        parent::bindEvent();
        $this->bindHttpEvent();
    }

    /**
     * Clean the remain of request
     * @param $request
     */
    public function cleanRequest($request)
    {
        // Clean Lumen session
        if ($request->hasSession()) {
            $session = $request->getSession();
            if (method_exists($session, 'clear')) {
                $session->clear();
            } elseif (method_exists($session, 'flush')) {
                $session->flush();
            }
            // TODO: clear session for other versions
        }

        // Clean Lumen cookie queue
        if (isset($this->app['cookie'])) {
            /**
             * @var \Illuminate\Contracts\Cookie\QueueingFactory $cookies
             */
            $cookies = $this->app['cookie'];
            foreach ($cookies->getQueuedCookies() as $name => $cookie) {
                $cookies->unqueue($name);
            }
        }

        // Re-register some singleton providers
        if (class_exists('\Illuminate\Auth\AuthServiceProvider', false)) {
            $this->app->register('\Illuminate\Auth\AuthServiceProvider', [], true);
            Facade::clearResolvedInstance('auth');
            Facade::clearResolvedInstance('auth.driver');

            // for jwt auth
            if (class_exists('\Tymon\JWTAuth\Providers\LaravelServiceProvider', false)) {
                $this->app->register('\Tymon\JWTAuth\Providers\LaravelServiceProvider', [], true)->boot();
            }
            if (class_exists('\Tymon\JWTAuth\Providers\LumenServiceProvider', false)) {
                $this->app->register('\Tymon\JWTAuth\Providers\LumenServiceProvider', [], true)->boot();
            }

            // for passport
            if (class_exists('\Laravel\Passport\PassportServiceProvider', false)) {
                $this->app->register('\Laravel\Passport\PassportServiceProvider', [], true)->boot();
            }
        }
        if (class_exists('\Illuminate\Auth\Passwords\PasswordResetServiceProvider', false)) {
            Facade::clearResolvedInstance('auth.password');
            $this->app->register('\Illuminate\Auth\Passwords\PasswordResetServiceProvider', [], true);
        }

        // Clear the request
        $this->app->forgetInstance('request');
        Facade::clearResolvedInstance('request');

        //...
    }
}
