<?php
/**
 * Created by PhpStorm.
 * User: liuwenbin
 * Date: 2018/3/21
 * Time: 下午5:10
 */

use Lamens\Lamens;

$input = file_get_contents('php://stdin');
$config = json_decode($input, true);
require_once $config['root_path'] . '/vendor/autoload.php';
$server = Lamens::getInstance($config);
$server->run();
