<?php 

namespace Epesi\Base\Dashboard\Integration\Joints;

use Epesi\Core\System\Modules\ModuleJoint;
use Epesi\Base\Dashboard\View\Applet;
use Epesi\Core\System\Modules\Concerns\HasOptions;

abstract class AppletJoint extends ModuleJoint
{
	use HasOptions;
	
	/**
	 * Caption to display on the applet
	 */
	abstract public function caption();
	
	/**
	 * Description of the applet purpose
	 */
	abstract public function info();
	
	/**
	 * Define the full screen link
	 */
	public function go() {}
	
	/**
	 * Define the applet body
	 * 
	 * Use the $applet parameter to 
	 * 		- add content to the applet $applet->add(['View']);, etc
	 * 		- add actions $applet->addAction('save')->link('some/link');
	 */
	abstract public function body(Applet $applet, $options = []);
}