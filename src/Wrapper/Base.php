<?php
/**
 * Created by PhpStorm.
 * User: liuwenbin
 * Date: 2018/3/21
 * Time: 下午5:13
 */

namespace Lamens\Wrapper;

use Lamens\Swoole\Swoole;

class Base extends Swoole {

    /**
     * @var \Laravel\Lumen\Application
     */
    protected $app;

    /**
     * Base constructor.
     *
     * @param array $conf
     */
    public function __construct($conf) {
        parent::__construct($conf);
    }

    /**
     * The callback function of Swoole for worker start event.
     *
     * @param \swoole_server $server
     * @param int $workerId
     */
    public function onWorkerStart($server, $workerId) {
        parent::onWorkerStart($server, $workerId);

        foreach (spl_autoload_functions() as $function) {
            spl_autoload_unregister($function);
        }

        $this->prepare();
    }

    /**
     * Bind the Swoole events.
     */
    protected function bindEvent() {
        $this->bindBaseEvent();
    }

    /**
     * Start Swoole server.
     */
    public function start() {
        $this->server->set($this->conf['swoole']);
        $this->bindEvent();
        if (isset($this->conf['swoole']['task_worker_num'])) {
            $this->bindTaskEvent();
        }
        $this->server->start();
    }

    /**
     * Load the autoload file.
     *
     * @param $rootPath
     */
    protected function autoload($rootPath) {
        if (file_exists($file = $rootPath . '/vendor/autoload.php')) {
            require_once $file;
        } else {
            require_once $rootPath . '/bootstrap/autoload.php';
        }
    }

    /**
     * Create the Lumen application.
     *
     * @param $rootPath
     * @return \Laravel\Lumen\Application
     */
    protected function createApp($rootPath) {
        return require $rootPath . '/bootstrap/app.php';
    }

    /**
     * Initialize Lumen environment.
     */
    protected function prepare() {
        $this->autoload($this->conf['root_path']);
        $this->app = $this->createApp($this->conf['root_path']);
        $this->app->instance('server', $this->server);
    }
}