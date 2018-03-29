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
        event('lamens.work_start', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function onTask($server, $taskId, $srcWorkerId, $data) {
        event('lamens.task', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function onFinish($server, $taskId, $data) {
        event('lamens.finish', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function onPipeMessage($server, $srcWorkerId, $data) {
        event('lamens.pipe_message', func_get_args());
    }

    /**
     * Bind the Swoole events.
     */
    protected function bindEvent() {
        $this->bindBaseEvent();
    }

    /**
     * Fire events before swoole start.
     */
    protected function fireServerStarting() {
        if (isset($this->conf['callbacks']['server_starting'])) {
            foreach ($this->conf['callbacks']['server_starting'] as $callback) {
                $callback();
            }
        }
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
        $this->fireServerStarting();
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
        $this->app->instance('server', $this->server);
    }
}