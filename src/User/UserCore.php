<?php

namespace Epesi\Base\User;

use Epesi\Core\System\Integration\Modules\ModuleCore;

class UserCore extends ModuleCore
{
	protected static $alias = 'user';

	protected static $requires = [
			\Epesi\Base\User\Access\AccessCore::class,
			\Epesi\Base\User\Online\OnlineCore::class,
			\Epesi\Base\User\Settings\SettingsCore::class,
	];
	
	public function install()
	{
		
	}

	public function uninstall()
	{
		
	}
}
