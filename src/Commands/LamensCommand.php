<?php
/**
 * Created by PhpStorm.
 * User: liuwenbin
 * Date: 2018/3/21
 * Time: 下午5:22
 */

namespace Lamens\Commands;

use Exception;
use ReflectionClass;
use Illuminate\Console\Command;
use Lamens\Wrapper\ServerInterface;

class LamensCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lamens {action : start | stop | restart | status | reload | reload_task}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lamens control utilities';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle() {
        try {
            $this->checkEnvironment();
            $action = $this->argument('action');
            $this->execAction($action);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Execute specified action.
     *
     * @param string $action
     * @throws Exception
     */
    public function execAction($action) {
        switch ($action) {
            case 'start':
                $this->start();
                break;
            case 'restart':
                $this->stop();
                $this->start();
                break;
            case 'stop':
                $this->stop();
                break;
            case 'status':
                $this->status();
                break;
            case 'reload':
            case 'reload_task':
                $map = [
                    'reload' => SIGUSR1,
                    'reload_task' => SIGUSR2,
                ];
                $this->sendSignal($map[$action]);
                $this->info("lamens $action successfully.");
                break;
            default:
                throw new Exception("Invalid argument '{$action}'.\n" .
                    "Expected 'start | stop | reload | reload_task | restart | quit'.");
        }
    }

    /**
     * Check operating environment.
     *
     * @throws Exception
     */
    protected function checkEnvironment() {
        if (!extension_loaded('swoole')) {
            throw new Exception('Failed! Need swoole extension.');
        }
        if (PHP_INT_MAX != 9223372036854775807) {
            throw new Exception('Failed! Need 64-bit operating system.');
        }
    }

    /**
     * Send signal to process
     *
     * @param int $sig
     * @return string
     * @throws Exception
     */
    protected function sendSignal($sig) {
        if ($pid = $this->getPid()) {
            posix_kill($pid, $sig);
            return $pid;
        } else {
            throw new Exception("Failed! There is no running lamens process.");
        }
    }

    /**
     * Stop lamens server.
     *
     * @throws Exception
     */
    protected function stop() {
        $pid = $this->sendSignal(SIGTERM);
        $cnt = 0;
        while (posix_kill($pid, 0) && $cnt < 10) {
            usleep(100000);
            $cnt++;
        }
        if ($cnt >= 10) {
            throw new Exception("Failed! Stopping lamens process timeout.");
        }
        if (file_exists(config('lamens.swoole.pid_file'))) {
            unlink(config('lamens.swoole.pid_file'));
        }
        $this->info("lamens stop successfully.");
    }

    /**
     * Output the base information of the server.
     */
    protected function outputServerInfo() {
        $this->info('Lamens: Speed up your Lumen with Swoole');
        $this->table(['Component', 'Version'], [
            ['Component' => 'PHP', 'Version' => phpversion()],
            ['Component' => 'Swoole', 'Version' => \swoole_version()],
            ['Component' => $this->getApplication()->getName(), 'Version' => $this->getApplication()->getVersion()],
        ]);
    }

    /**
     * Get status of lamens server.
     */
    protected function status() {
        if ($this->getPid()) {
            $this->info("lamens is running.");
        } else {
            $this->info("lamens has been stopped.");
        }
    }

    /**
     * Check server is startup or not.
     *
     * @throws Exception
     */
    protected function isStartup() {
        $pidFile = config('lamens.swoole.pid_file');
        $time = 0;
        while (!file_exists($pidFile) && $time <= 20) {
            usleep(100000);
            $time++;
        }
        if (!file_exists($pidFile)) {
            throw new Exception('Failed! Server process startup failure.');
        }
    }

    /**
     * Start lamens server.
     *
     * @throws Exception
     */
    protected function start() {
        $this->outputServerInfo();

        if ($this->getPid()) {
            throw new Exception('Failed! Lamens process is already running.');
        }

        $this->bootstrap();
        $this->isStartup();
        $this->info("lamens start successfully.");
    }

    /**
     * Create a new Lamens server.
     *
     * @param string $cmd
     * @param string|null $input
     * @return bool
     */
    protected function create($cmd, $input = null) {
        if (($handle = popen($cmd, 'w')) === false) {
            return false;
        }
        if ($input !== null) {
            fwrite($handle, $input);
        }
        pclose($handle);
        return true;
    }

    /**
     * Start lamens server.
     *
     * @throws Exception
     */
    protected function bootstrap() {
        app()->configure('lamens');
        $host = config('lamens.host');
        $port = config('lamens.port');
        $socket = @stream_socket_server("tcp://{$host}:{$port}");
        if (!$socket) {
            throw new Exception("Failed! Address {$host}:{$port} already in use.");
        } else {
            fclose($socket);
        }
        $wrapper = $this->getWrapper();
        $this->checkSwooleOptions($wrapper, config('lamens.swoole'));

        $conf = array_merge(config('lamens'), [
            'wrapper' => $wrapper,
            'root_path' => base_path(),
        ]);

        $cmd = sprintf('%s %s/../Entry.php', PHP_BINARY, __DIR__);
        if (!$this->create($cmd, json_encode($conf))) {
            throw new Exception("Failed! Lamens: popen $cmd failed");
        }
    }

    /**
     * Get the wrapper of server.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getWrapper() {
        $mode = config('lamens.mode');
        if (!$mode) {
            throw new Exception("Failed! Lamens needs running mode.");
        }

        $wrapper = "Lamens\\Wrapper\\{$mode}Wrapper";
        if (!class_exists($wrapper)) {
            throw new Exception("Failed! class $wrapper is not exist.");
        }
        return $wrapper;
    }

    /**
     * Check swoole options.
     *
     * @param string $wrapper
     * @param array $conf
     *
     * @throws Exception
     */
    protected function checkSwooleOptions($wrapper, $conf) {
        $options = $wrapper::getOptions();
        foreach ($conf as $k => $v) {
            if (!in_array($k, $options)) {
                throw new Exception("Failed! swoole option '$k' is not valid.");
            }
        }
    }

    /**
     * Get the process id of the current running lamens server.
     *
     * @return bool|int
     */
    protected function getPid() {
        $pidFile = config('lamens.swoole.pid_file');
        try {
            if (file_exists($pidFile)) {
                $pid = file_get_contents($pidFile);
                if ($pid === false) {
                    throw new Exception("Failed! Can not read pid file.");
                }
                $pid = trim($pid);
                if (!is_numeric($pid)) {
                    throw new Exception("Failed! process id is invalid.");
                }
                $pid = intval($pid);

                if ($this->isRunning($pid)) {
                    return $pid;
                } else {
                    throw new Exception("Failed! process id is not running.");
                }
            }
        } catch (Exception $e) {
            if (file_exists($pidFile)) {
                unlink($pidFile);
            }
        }
        return false;
    }

    /**
     * Check whether the process is running.
     * This function is not strictly correct.
     * The pid may exist but the process is owned by a user
     * other than the one you use to run the code, and you're not root,
     * in which case posix_kill will return false and you'll get an error
     * saying you're not allowed to signal that process (operation not permitted).
     * But in this scene we can think of this function as correct,
     * because all these processes belong to the same user.
     *
     * @param int $pid
     * @return bool
     */
    protected function isRunning($pid) {
        if (!$pid) {
            return false;
        }
        return posix_kill($pid, 0);
    }
}
