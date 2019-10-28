<?php

namespace Epesi\Base\User\Online;

use Epesi\Core\Integration\Module\ModuleCore;
use Epesi\Base\User\Online\Integration\Joints\UsersOnlineApplet;

class OnlineCore extends ModuleCore
{
	protected static $alias = 'users.online';
	
	protected static $joints = [
			UsersOnlineApplet::class
	];
	
	public function install()
	{
		
	}

	public function uninstall()
	{
		
	}
}
