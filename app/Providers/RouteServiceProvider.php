<?php

namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map(Router $router)
    {
        $this->mapInternalApiRoutes();
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapInternalApiRoutes()
    {
        Route::group(
            ['domain' => env('API_DOMAIN')],
            function () {
                Route::middleware('internal_api')->namespace($this->namespace)->group(base_path('routes/internal_api.php'));
            }
        );
    }

    /**
     * 业务api路由
     */
    protected function mapApiRoutes()
    {
        Route::group(
            ['domain' => env('API_DOMAIN')],
            function () {
                Route::middleware('api')
                    ->namespace($this->namespace)
                    ->group(base_path('routes/api.php'));
            }
        );
    }
}
