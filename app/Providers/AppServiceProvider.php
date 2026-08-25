<?php

namespace App\Providers;

use App\Services\AIConfigurationManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // AIConfigurationManager es un Singleton clásico (constructor
        // privado + getInstance()). Este binding solo le permite al
        // contenedor de Laravel entregar la MISMA instancia cuando algún
        // servicio la pide por inyección de dependencias; la unicidad de
        // la instancia la sigue garantizando la propia clase, no Laravel.
        $this->app->singleton(AIConfigurationManager::class, fn () => AIConfigurationManager::getInstance());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
