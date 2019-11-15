<?php

namespace Epesi\Base\Dashboard;

use Epesi\Core\System\Integration\Modules\ModuleCore;
use Epesi\Core\System\User\Database\Models\User;
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
		// setup default dashboard
		Dashboard::create([
				'user_id' => 0,
				'name' => __('Admin Default')
		])->applets()->create([
				'class' => 'Epesi\\Applets\\Clock\\ClockApplet',
				'row' => 0,
				'column' => 3,
		]);
	}
	
	public static function info()
	{
		return [
				__('Author') => 'Georgi Hristov',
				__('Copyright') => 'X Systems Ltd',
				'',
				'Provides dashboard functionality'
		];
	}
	
	public static function boot()
	{
		// create user default dashboard as copy of the system default
		User::created(function(User $user) {
			if (! $defaultDashboard = Dashboard::where('user_id', 0)->first()) return;
			
			$userDefaultDashboard = $defaultDashboard->replicate();
			
			$userDefaultDashboard->name = __('Default');
			$userDefaultDashboard->user_id = $user->id;
			
			$userDefaultDashboard->save();
			
			foreach ($defaultDashboard->applets()->get() as $defaultApplet) {
				$userApplet = $defaultApplet->replicate();
				
				$userApplet->dashboard()->associate($userDefaultDashboard)->save();
			}
		});
	}
}
