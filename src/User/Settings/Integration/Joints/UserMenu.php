<?php 

namespace Epesi\Base\User\Settings\Integration\Joints;

use Epesi\Core\Integration\Joints\UserMenuJoint;

class UserMenu extends UserMenuJoint
{
	public function entries() {
		return [
				'user.settings' => [
						'item' => [__('User Settings'), 'icon' => 'settings', 'class' => ['pjax']],
						'action' => url('view/user.settings'),
						'group' => '10000:user'
				]
		];
	}
}