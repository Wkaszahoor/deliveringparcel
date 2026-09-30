<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PermissionsServiceProvider extends ServiceProvider
{
   
    public function register()
    {
        //
    }

    public function boot()
    {
        try {
            // Cached so request boot doesn't query the DB on every poll/request;
            // during DB connection exhaustion (Namecheap max_user_connections=30)
            // this lets the app boot from cache instead of failing.
            $permissions = Cache::remember('permissions.gates', 60, function () {
                return Permission::get();
            });
            $permissions->map(function ($permission) {
                Gate::define($permission->slug, function ($user) use ($permission) {
                    return $user->hasPermissionTo($permission);
                });
            });
        } catch (\Exception $e) {
            report($e);
            return false;
        }

        //Blade directives
        Blade::directive('role', function ($role) {
             return "if(auth()->check() && auth()->user()->hasRole({$role})) :"; //return this if statement inside php tag
        });

        Blade::directive('endrole', function ($role) {
             return "endif;"; //return this endif statement inside php tag
        });

    }
}
