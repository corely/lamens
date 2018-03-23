<?php
/**
 * Created by PhpStorm.
 * User: liuwenbin
 * Date: 2018/3/21
 * Time: 下午5:22
 */

namespace Lamens\Swoole;


class Swoole {

    /**
     * @var \swoole_server
     */
    protected $server;

    /**
     * @var array
     */
    protected $conf;

    /**
     * Swoole configuration options.
     * @see http://wiki.swoole.com/wiki/page/274.html
     *
     * @return array
     */
    public static function getParams() {
        return [
            'reactor_num',
            'worker_num',
            'max_request' => 2000,
            'max_conn',
            'task_worker_num',
            'task_ipc_mode',
            'task_max_request',
            'task_tmpdir',
            'dispatch_mode',
            'dispatch_func',
            'message_queue_key',
            'daemonize' => 1,
            'backlog',
            'log_file' => [self::class, 'getLogFile'],
            'log_level',
            'heartbeat_check_interval',
            'heartbeat_idle_time',
            'open_eof_check',
            'open_eof_split',
            'open_length_check',
            'package_eof',
            'package_length_type',
            'package_length_func',
            'package_max_length',
            'open_cpu_affinity',
            'cpu_affinity_ignore',
            'open_tcp_nodelay',
            'tcp_defer_accept',
            'ssl_cert_file',
            'ssl_method',
            'ssl_ciphers',
            'user',
            'group',
            'chroot',
            'pid_file',
            'pipe_buffer_size',
            'buffer_output_size',
            'socket_buffer_size',
            'enable_unsafe_event',
            'discard_timeout_request',
            'enable_reuse_port',
            'enable_delay_receive',
            'open_http_protocol',
            'open_http2_protocol',
            'open_websocket_protocol',
            'open_mqtt_protocol',
            'reload_async',
            'tcp_fastopen',
            'max_wait_time',
        ];
    }

    /**
     * Swoole constructor.
     *
     * @param array $conf
     */
    public function __construct($conf) {
        $this->conf = $conf;
    }

    /**
     * Bind base events of Swoole.
     */
    protected function bindBaseEvent() {
        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('shutdown', [$this, 'onShutdown']);
        $this->server->on('managerStart', [$this, 'onManagerStart']);
        $this->server->on('managerStop', [$this, 'onManagerStop']);
        $this->server->on('workerStart', [$this, 'onWorkerStart']);
        $this->server->on('workerStop', [$this, 'onWorkerStop']);
    }

    /**
     * Bind http event of Swoole.
     */
    protected function bindHttpEvent() {
        $this->server->on('request', [$this, 'onRequest']);
    }

    /**
     * Bind task event of Swoole.
     */
    protected function bindTaskEvent() {
        $this->server->on('task', [$this, 'onTask']);
        $this->server->on('finish', [$this, 'onFinish']);
    }

    /**
     * The callback function of Swoole for master start event.
     *
     * @param \swoole_server $server
     */
    public function onStart($server) {
        $name = sprintf('%s: master process', $this->conf['server']);
        $this->setProcessName($name);
    }

    /**
     * The callback function of Swoole for master stop event.
     *
     * @param \swoole_server $server
     */
    public function onShutdown($server) {

    }

    /**
     * The callback function of Swoole for manager start event.
     *
     * @param \swoole_server $server
     */
    public function onManagerStart($server) {
        $name = sprintf('%s: manager process', $this->conf['server']);
        $this->setProcessName($name);
    }

    /**
     * The callback function of Swoole for manager stop event.
     *
     * @param \swoole_server $server
     */
    public function onManagerStop($server) {

    }

    /**
     * The callback function of Swoole for worker start event.
     *
     * @param \swoole_server $server
     * @param int $workerId
     */
    public function onWorkerStart($server, $workerId) {
        $process = $workerId >= $server->setting['worker_num'] ? 'task' : 'worker';
        $name = sprintf('%s: %s process %d', $this->conf['server'], $process, $workerId);
        $this->setProcessName($name);

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }
        if (function_exists('apcu_clear_cache')) {
            apcu_clear_cache();
        }

        clearstatcache();
    }


    /**
     * The callback function of Swoole for worker stop event.
     *
     * @param \swoole_server $server
     * @param int $workerId
     */
    public function onWorkerStop($server, $workerId) {

    }

    /**
     * The callback function of Swoole for task event.
     *
     * @param \swoole_server $server
     * @param int $taskId
     * @param int $srcWorkerId
     * @param mixed $data
     */
    public function onTask($server, $taskId, $srcWorkerId, $data) {

    }

    /**
     * The callback function of Swoole for task finish event.
     *
     * @param \swoole_server $server
     * @param int $taskId
     * @param mixed $data
     */
    public function onFinish($server, $taskId, $data) {

    }

    /**
     * The callback function of Swoole for pipe message event.
     *
     * @param \swoole_server $server
     * @param int $srcWorkerId
     * @param mixed $data
     */
    public function onPipeMessage($server, $srcWorkerId, $data) {

    }

    /**
     * The callback function of Swoole for http request event.
     *
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     */
    public function onRequest($request, $response) {

    }

    /**
     * Set specified name of Swoole process.
     *
     * @param string $name
     */
    protected function setProcessName($name) {
        if (PHP_OS === 'Darwin') {
            return;
        }
        \swoole_set_process_name($name);
    }
}