<?php

declare(strict_types=1);

namespace Briskbase\Bridge;

use Briskbase\Bridge\Http\BridgeClient;
use Illuminate\Support\ServiceProvider;

final class BridgeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/bridge.php', 'bridge');

        $this->app->singleton(BridgeClient::class, function ($app) {
            $config = $app['config']['bridge'];

            return new BridgeClient(
                baseUrl: rtrim((string) $config['url'], '/'),
                keyId: (string) $config['key_id'],
                secret: (string) $config['secret'],
                timeout: (int) $config['timeout'],
                defaultCurrency: (string) $config['currency'],
                defaultCountry: (string) $config['country'],
                verifySsl: (bool) ($config['verify_ssl'] ?? true),
                webhookSecret: (string) ($config['webhook_secret'] ?? ''),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/bridge.php' => config_path('bridge.php'),
            ], 'bridge-config');
        }
    }
}
