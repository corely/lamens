<?php
/**
 * Created by PhpStorm.
 * User: liuwenbin
 * Date: 2018/3/21
 * Time: 下午5:10
 */

use Lamens\Lamens;

// Register temporary autoload function.
spl_autoload_register(function ($class) {
    $prefixLen = strlen('Lamens\\');
    $file = __DIR__ . '/' . substr(str_replace('\\', '/', $class), $prefixLen) . '.php';
    if (is_readable($file)) {
        require $file;
        return true;
    }
    return false;
});

$input = file_get_contents('php://stdin');
$config = json_decode($input, true);
$server = Lamens::getInstance($config);
$server->run();
