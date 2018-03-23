<?php
/**
 * Created by PhpStorm.
 * User: liuwenbin
 * Date: 2018/3/21
 * Time: 下午5:14
 */

namespace Lamens;


class Lamens {

    /**
     * Shared instance.
     *
     * @var static
     */
    protected static $instance;

    /**
     * Swoole server wrapper.
     *
     * @var string
     */
    protected $wrapper;

    /**
     * Lamens constructor.
     * @param $config
     */
    public function __construct($config) {
        $wrapper = $config['wrapper'];
        $this->wrapper = new $wrapper($config);
    }

    /**
     * Get shared instance.
     *
     * @param array $config
     * @return Lamens
     */
    public static function getInstance($config) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Run the server.
     */
    public function run() {
        $this->wrapper->start();
    }

}
