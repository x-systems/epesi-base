<?php

namespace Epesi\Base\Dashboard;

use Epesi\Core\System\Modules\ModuleCore;
use Epesi\Core\System\User\Database\Models\User;

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
	    Models\Dashboard::migrate();
	    Models\DashboardApplet::migrate();
		
		// setup default dashboard
	    $dashboardId = (int) Models\Dashboard::create()->insert([
				'user_id' => 0,
				'name' => __('Admin Default')
		]);
		
	    Models\DashboardApplet::create()->insert([
	            [
	                    'dashboard_id' => $dashboardId,
	                    'class' => 'Epesi\\Applets\\Clock\\ClockApplet',
	                    'row' => 0,
	                    'column' => 3,
	            ]
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
		    if (! $defaultDashboard = Models\Dashboard::create()->addCondition('user_id', 0)->tryLoadAny()) return;
			$userDefaultDashboard = (clone $defaultDashboard)->duplicate()->save([
			        'name' => __('Default'),
			        'user_id' => $user->id
			]);

			foreach ($defaultDashboard->ref('applets') as $defaultApplet) {
			    $defaultApplet->duplicate()->saveAndUnload(['dashboard_id' => $userDefaultDashboard->id]);
			}
		});
	}
}
