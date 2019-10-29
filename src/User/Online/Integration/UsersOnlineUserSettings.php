<?php 

namespace Epesi\Base\Dashboard\Integration;

use Epesi\Base\User\Settings\Integration\Joints\UserSettingsJoint;
use Epesi\Base\User\Online\OnlineCore;

class UsersOnlineUserSettings extends UserSettingsJoint
{
	public function group()
	{
		return OnlineCore::alias();
	}
	
	public function label()
	{
		return __('Misc');
	}

	public function icon()
	{
		return 'list';
	}

	public function elements() {
		return [
				[
						'name' => 'show_me',
						'decorator' => [
								'Checkbox',
								'caption' => __('Show me in online users'),
						],
						'default' => 1,						
				],
		];
	}
}