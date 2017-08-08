<?php

namespace Eddie\Ticket;


use Illuminate\Support\ServiceProvider;

class TicketServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;


    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->handleConfigs();
        // $this->handleMigrations();
        // $this->handleViews();
         $this->handleTranslations();
        // $this->handleRoutes();

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Bind any implementations.
        $this->app->singleton('Eddie\Ticket\TicketManager', function ($app) {
            return new TicketManager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['Eddie\Ticket\TicketManager'];
    }


    private function handleConfigs()
    {
        /*
         * Config path.
         */
        $configPath = realpath(__DIR__ . '/../config/ticket.php');

        /*
         * Publish config file.
         */
        $this->publishes([$configPath => config_path('ticket.php')]);

        /*
         * Merge config file.
         */
        $this->mergeConfigFrom($configPath, 'ticket.php');
    }

    private function handleTranslations()
    {
        $this->loadTranslationsFrom(realpath(__DIR__ . '/../lang'), 'ticket');
    }

    private function handleViews()
    {
        /*
         * TODO ...
         */
        //$this->loadViewsFrom(__DIR__.'/../views', 'packagename');
        //$this->publishes([__DIR__.'/../views' => base_path('resources/views/vendor/packagename')]);
    }

    private function handleMigrations()
    {
        $this->publishes([__DIR__ . '/../migrations' => base_path('database/migrations')]);
    }

    private function handleRoutes()
    {
        include __DIR__.'/../routes.php';
    }
}