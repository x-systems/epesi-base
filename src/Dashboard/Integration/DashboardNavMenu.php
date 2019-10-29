<?php 

namespace Epesi\Base\Dashboard\Integration;

use Epesi\Base\Layout\Integration\Joints\NavMenuJoint;
use Epesi\Base\Dashboard\Database\Models\Dashboard;
use Illuminate\Support\Facades\Auth;
use Epesi\Core\Integration\ModuleView;

class DashboardNavMenu extends NavMenuJoint
{
	public function items()
	{
		$ret = [];
		
		foreach (Dashboard::where(['user_id' => Auth::user()->id])->orderBy('position')->get() as $dashboard) {
			$ret[$dashboard->name] = [
					'access' => true,
					'weight' => $dashboard['position'],
					'link' => ModuleView::moduleLink('dashboard', 'body', ['dashboard' => $dashboard->id])
			];
		}

		return [
				__('DASHBOARD') => count($ret) > 1? [
						'access' => true,
						'group' => $ret,
						'weight' => -10000
				]: array_merge(reset($ret), ['weight' => -10000]),
		];
	}
}