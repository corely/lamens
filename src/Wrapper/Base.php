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
     * {@inheritdoc}
     */
    public function onWorkerStart($server, $workerId) {
        parent::onWorkerStart($server, $workerId);
        $this->prepare();
        event('lamens.work_start', func_get_args(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function onTask($server, $taskId, $srcWorkerId, $data) {
        $ret = event('lamens.task', func_get_args(), true);
        if (!is_null($ret)) {
            return $ret;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onFinish($server, $taskId, $data) {
        event('lamens.finish', func_get_args(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function onPipeMessage($server, $srcWorkerId, $data) {
        event('lamens.pipe_message', func_get_args(), true);
    }

    /**
     * Fire events before swoole start.
     */
    protected function fireServerStarting() {
        if (isset($this->conf['callbacks']['server_starting'])) {
            foreach ($this->conf['callbacks']['server_starting'] as $callback) {
                $callback($this->server, $this->conf);
            }
        }
    }

    protected function bindProcess() {
        if (isset($this->conf['swoole']['process'])) {
            $callback = $this->conf['swoole']['process'];
            $process = new \swoole_process(function ($process) use ($callback) {
                $this->prepare();
                $callback($this->server, $process);
            });

            $this->server->addProcess($process);
        }
    }

    /**
     * Start Swoole server.
     */
    public function start() {
        $this->server->set($this->conf['swoole']['settings']);
        $this->bindEvent();
        $this->fireServerStarting();
        $this->bindProcess();
        $this->server->start();
    }

    /**
     * Initialize Lumen environment.
     */
    protected function prepare() {
        // Create the Lumen application
        $this->app = require $this->conf['root_path'] . '/bootstrap/app.php';
        // Load configuration
        $this->app->configure('lamens');
        // Bind swoole instance
        $this->app->instance('lamens.server', $this->server);
    }
}