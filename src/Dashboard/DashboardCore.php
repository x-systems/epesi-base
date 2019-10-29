<?php

namespace Epesi\Base\Dashboard;

use Epesi\Core\Integration\ModuleCore;
use Epesi\Base\Dashboard\Integration\DashboardUserSettings;
use Epesi\Base\Dashboard\Integration\DashboardSystemSettings;
use Epesi\Base\Dashboard\Integration\DashboardNavMenu;

class DashboardCore extends ModuleCore
{
	protected static $alias = 'dashboard';
	
	protected static $joints = [
			DashboardUserSettings::class,
			DashboardSystemSettings::class,
			DashboardNavMenu::class
	];
	
	public function install()
	{
		
	}
	
	public function uninstall()
	{
		
	}
}
