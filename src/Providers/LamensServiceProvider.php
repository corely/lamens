<?php
/**
 * Created by PhpStorm.
 * User: liuwenbin
 * Date: 2018/3/21
 * Time: 下午5:22
 */

namespace Lamens\Providers;

use Lamens\Commands\LamensCommand;
use Lamens\Commands\VendorPublishCommand;
use Illuminate\Support\ServiceProvider;

class LamensServiceProvider extends ServiceProvider
{

    /**
     * Publish the configuration file of Lamens to config folder.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/lamens.php' => base_path('config/lamens.php'),
        ]);

    }

    /**
     * Register Lamens command to application.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/lamens.php', 'lamens'
        );

        $this->commands([LamensCommand::class]);
    }

}