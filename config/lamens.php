<?php

return [
    'host' => env('LAMENS_HOST', '127.0.0.1'),

    'port' => env('LAMENS_PORT', 5050),

    'enable_gzip' => extension_loaded('zlib') && env('LAMENS_ENABLE_GZIP', 1),

    // lamens modes:
    // Http        uses swoole to response http requests
    'mode' => env('LAMENS_MODE', 'Http'),

    // server name
    'server' => env('LAMENS_SERVER', 'Lamens'),

    // Swoole settings
    'swoole' => [
        'daemonize' => env('LAMENS_DAEMONIZE', true),
        'max_request' => env('LAMENS_MAX_REQUEST', 2000),
        'worker_num' => 2,
        'task_worker_num' => 2,
        'pid_file' => storage_path('app/lamens.pid'),
        'log_file' => storage_path('logs/swoole.log'),
        'log_level' => 2,
        'document_root' => base_path('public'),
        'reload_async' => true,
        'max_wait_time' => 60,

        /**
         * More settings of Swoole
         * @see https://wiki.swoole.com/wiki/page/274.html  Chinese
         * @see https://www.swoole.co.uk/docs/modules/swoole-server/configuration  English
         */
    ],
];