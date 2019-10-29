<?php

namespace Epesi\Base\User\Settings;

use Epesi\Core\Integration\ModuleCore;
use Epesi\Base\User\Settings\Integration\UserMenu;

class SettingsCore extends ModuleCore
{
	protected static $alias = 'user.settings';
	
	protected static $joints = [
			UserMenu::class
	];
	
	public function install()
	{
		
	}

	public function uninstall()
	{
		
	}
}