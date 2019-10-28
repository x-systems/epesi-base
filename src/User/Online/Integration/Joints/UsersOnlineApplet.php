<?php 

namespace Epesi\Base\User\Online\Integration\Joints;

use Epesi\Base\Dashboard\Integration\Joints\AppletJoint;
use Epesi\Base\Dashboard\UI\Seeds\Applet;
use Epesi\Core\Integration\Concerns\HasOptions;

class UsersOnlineApplet extends AppletJoint
{
	use HasOptions;
	
	public function caption()
	{
		return __('Users Online');
	}
	
	public function info()
	{
		return __('Shows users currently online');
	}

	public function body(Applet $applet, $options = [])
	{
		
	}
}