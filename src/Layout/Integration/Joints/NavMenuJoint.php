<?php 

namespace Epesi\Base\Layout\Integration\Joints;

use Epesi\Core\Integration\ModuleJoint;

abstract class NavMenuJoint extends ModuleJoint
{
	abstract public function items();
}