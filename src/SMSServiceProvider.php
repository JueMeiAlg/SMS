<?php

namespace Alg\SMS;

use Alg\SMS\Facades\SMS;
use Illuminate\Support\ServiceProvider;

class SMSServiceProvider extends ServiceProvider
{
    protected $defer = true; // 延迟加载服务

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // 单例绑定服务
        $this->app->singleton('sms', function ($app) {
            return new SMS();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Config/sms.php' => config_path('sms.php'), // 发布配置文件到 laravel 的config 下
        ]);
    }

    public function provides()
    {
        return ['sms'];
    }
}
