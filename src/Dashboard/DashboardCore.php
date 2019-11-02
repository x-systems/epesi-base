<?php

namespace Epesi\Base\Dashboard;

use Epesi\Core\System\Integration\Modules\ModuleCore;
use Epesi\Base\User\Database\Models\User;
use Epesi\Base\Dashboard\Database\Models\Dashboard;

class DashboardCore extends ModuleCore
{
	protected static $alias = 'dashboard';
	
	protected static $joints = [
			Integration\DashboardUserSettings::class,
			Integration\DashboardSystemSettings::class,
			Integration\DashboardNavMenu::class,
			Integration\DashboardHomePage::class
	];
	
	public function install()
	{
		
	}
	
	public function uninstall()
	{
		
	}
	
	public static function boot()
	{
		// create user default dashboard
		User::created(function(User $user) {
			$defaultDashboard = Dashboard::where('user_id', 0)->first();
			
			$userDefaultDashboard = clone $defaultDashboard;
			
			$userDefaultDashboard->name = __('Default');
			$userDefaultDashboard->user_id = $user->id;
			
			$userDefaultDashboard->save();
		});
	}
}
