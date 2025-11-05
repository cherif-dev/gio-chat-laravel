<?php

namespace GenitIo\Chat;

use GenitIo\Chat\Services\ChatService;
use GenitIo\Chat\Services\GenitIoApiClient;
use Illuminate\Support\ServiceProvider;

class ChatServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Publish migrations
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'chat-migrations');

            // Publish config
            $this->publishes([
                __DIR__ . '/../config/chat.php' => config_path('chat.php'),
            ], 'chat-config');
        }

        // Load config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/chat.php',
            'chat'
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Register the GenitIoApiClient as a singleton
        $this->app->singleton('api.genit.io', function ($app) {
            return new GenitIoApiClient();
        });

        // Register the ChatService as a singleton
        $this->app->singleton('chat', function ($app) {
            return new ChatService($app->make('api.genit.io'));
        });

        // Register ChatService binding for dependency injection
        $this->app->bind(ChatService::class, function ($app) {
            return $app->make('chat');
        });

        // Register GenitIoApiClient binding for dependency injection
        $this->app->bind(GenitIoApiClient::class, function ($app) {
            return $app->make('api.genit.io');
        });
    }
}