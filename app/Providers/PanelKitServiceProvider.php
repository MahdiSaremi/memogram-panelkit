<?php

namespace MemoGram\PanelKit\Providers;

use Illuminate\Support\ServiceProvider;
use MemoGram\PanelKit\PanelKit;

class PanelKitServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        // $this->registerCommands();
        // $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = __DIR__ . '/../../lang';
        $this->publishes([$langPath => lang_path('vendor/panelkit')], ['lang', 'panelkit:lang']);


        // todo
        // if (is_dir($langPath)) {
        $this->loadTranslationsFrom($langPath, 'panelkit');
        $this->loadJsonTranslationsFrom($langPath);
        // } else {
        //     $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
        //     $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        // }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([__DIR__ . '/../../config/config.php' => config_path('panelkit.php')], ['config', 'panelkit:config']);
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'panelkit');

        $this->registerSettings();
    }

    protected function registerSettings()
    {
        if ($user = config('panelkit.user')) {
            PanelKit::setUserClass($user);
        }
    }
}
