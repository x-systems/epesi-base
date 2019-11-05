<?php 

namespace Epesi\Base\Dashboard\Integration;

use Epesi\Core\HomePage\Integration\Joints\HomePageJoint;

class DashboardHomePage extends HomePageJoint
{
	public function caption()
	{
		return __('Dashboard');
	}
	
	public function link()
	{
		return 'view/dashboard';
	}
}