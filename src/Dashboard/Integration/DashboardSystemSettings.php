<?php 

namespace Epesi\Base\Dashboard\Integration;

use Epesi\Core\System\Integration\Joints\SystemSettingsJoint;

class DashboardSystemSettings extends SystemSettingsJoint
{
	public function label()
	{
		return __('Dashboard');
	}

	public function icon()
	{
		return 'desktop';
	}
	
	public function link() {
		return ['dashboard', 'editDashboard', ['admin' => true, 'label' => __('System Settings')]];
	}
}