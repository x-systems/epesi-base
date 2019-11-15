<?php 

namespace Epesi\Base\CommonData\Integration;

use Epesi\Core\System\Integration\Joints\SystemSettingsJoint;

class CommonDataSystemSettings extends SystemSettingsJoint
{
	public function section()
	{
		return __('Data');
	}
	
	public function label()
	{
		return __('Common Data');
	}

	public function icon()
	{
		return 'list';
	}
	
	public function link() {
		return ['commondata'];
	}
}