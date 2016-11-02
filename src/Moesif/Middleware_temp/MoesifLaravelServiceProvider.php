<?php
namespace Moesif\Middleware;
use Illuminate\Support\ServiceProvider;

class MoesifLaravelServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/moesif.php' => config_path('moesif.php'),
        ]);
    }
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        // $this->mergeConfigFrom(
        //     __DIR__.'/config/moesif.php', 'moesif'
        // );
    }
}
