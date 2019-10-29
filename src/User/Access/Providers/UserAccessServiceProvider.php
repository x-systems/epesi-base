<?php

namespace Epesi\Base\User\Access\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class UserAccessServiceProvider extends ServiceProvider
{
    /**
     * Booting the package.
     */
    public function boot()
    {
    	// allow Super Admin full access
    	Gate::after(function ($user, $ability) {
    		return $user->hasRole('Super Admin');
    	});
    }
}
