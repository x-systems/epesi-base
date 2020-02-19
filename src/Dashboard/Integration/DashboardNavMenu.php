<?php 

namespace Epesi\Base\Dashboard\Integration;

use Epesi\Core\Layout\Integration\Joints\NavMenuJoint;
use Illuminate\Support\Facades\Auth;
use Epesi\Core\System\Modules\ModuleView;
use Epesi\Base\Dashboard\Models\Dashboard;

class DashboardNavMenu extends NavMenuJoint
{
	public function items()
	{
		$ret = [];
		
		foreach (Dashboard::create()->addCondition('user_id', Auth::id())->setOrder('position') as $dashboard) {
			$ret[$dashboard['name']] = [
					'access' => true,
					'weight' => $dashboard['position'],
					'action' => ModuleView::moduleLink('dashboard', 'body', ['dashboard' => $dashboard->id])
			];
		}

		return $ret? [
				__('DASHBOARD') => count($ret) > 1? [
				        'item' => ['icon' => 'tachometer alternate'],
						'access' => true,
						'group' => $ret,
						'weight' => -10000
				]: array_merge(reset($ret), ['weight' => -10000]),
		]: [];
	}
}