<?php

namespace Epesi\Base\Dashboard;

use Epesi\Core\Integration\Module\ModuleCore;
use Epesi\Base\Dashboard\Integration\Joints\DashboardUserSettings;
use Epesi\Base\Dashboard\Integration\Joints\DashboardSystemSettings;
use Epesi\Base\Dashboard\Integration\Joints\DashboardNavMenu;

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
