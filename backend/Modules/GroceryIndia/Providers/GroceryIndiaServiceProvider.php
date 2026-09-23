<?php

namespace Modules\GroceryIndia\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

use Modules\GroceryIndia\Console\CheckSubscriptionExpiry;
use Modules\GroceryIndia\Console\GenerateReports;
use Modules\GroceryIndia\Console\TriggerPushNotification;
use Modules\GroceryIndia\Console\ClearGroceryIndiaReports;
use Modules\GroceryIndia\Console\GenerateNotifications;
use Modules\GroceryIndia\Console\ClearItemsFromCart;
use Illuminate\Console\Scheduling\Schedule;

class GroceryIndiaServiceProvider extends ServiceProvider
{

    protected $commands = [
        CheckSubscriptionExpiry::class,
        GenerateReports::class,
        TriggerPushNotification::class,
        ClearGroceryIndiaReports::class,
        GenerateNotifications::class,
        ClearItemsFromCart::class,
    ];

    /**
     * @var string $moduleName
     */
    protected $moduleName = 'GroceryIndia';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'groceryindia';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        $this->commands($this->commands);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            // $schedule->command('groceryindia:generate-reports')
            //     ->everyFifteenMinutes()
            //     ->withoutOverlapping()
            //     ->runInBackground();

            $schedule->command('groceryindia:check-shop-subscription')
                ->everyThirtyMinutes()
                ->withoutOverlapping();

            // $schedule->command('groceryindia:check-shop-subscription')
            //     ->dailyAt('02:30');

            // $schedule->command('groceryindia:check-shop-subscription')
            //     ->dailyAt('03:00');

            $schedule->command('groceryindia:generate-notification')
                ->everyFiveMinutes()
                ->withoutOverlapping()
                ->runInBackground();

            $schedule->command('groceryindia:clear-items-from-cart')
                ->dailyAt('02:00')
                ->withoutOverlapping();

        });

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );

        // Merge all other config files in Config directory
        $configPath = module_path($this->moduleName, 'Config');
        foreach (glob($configPath . '/*.php') as $file) {
            $name = basename($file, '.php'); // e.g. sms.php => sms
            $this->mergeConfigFrom($file, $name);
        }
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'Resources/lang'));
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
