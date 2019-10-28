<?php

namespace Epesi\Base\Dashboard\UI\Seeds;

use atk4\ui\View;

class Applet extends View
{
	public $ui = 'applet segment raised';
	public $defaultTemplate = __DIR__ . '/../Templates/applet.html';
	
	protected $appletId;
	protected $jointClass;
	protected $options;
	protected $admin = false;
	protected $locked = false;
	
	public function renderView()
	{		
		/**
		 * @var \Epesi\Base\Dashboard\Integration\Joints\AppletJoint $joint
		 */
		$joint = new $this->jointClass();

		$this->set('Name', $joint->caption())->setAttr('applet-id', $this->appletId);
		
		if ($this->admin) {
			$this->set($joint->info())->setAttr('searchkey', strtolower($joint->caption() . ';' . $joint->info()));
		}
		else {
			if (! $this->locked) {
				$this->addControl('close', 'applet-close')->setAttr('title', __('Close applet'));
			}
			
			$this->addControl('ellipsis vertical')->setAttr('title', __('Applet settings'))->link($this->app->moduleLink('dashboard', 'showSettings', [$this->appletId]));
			
			if ($link = $joint->go()) {
				$this->addControl('expand')->setAttr('title', __('Full screen'))->link($link);
			}			

			ob_start();
			$joint->body($this, $this->options?? $joint->defaultOptions());

			if ($content = ob_get_clean()) {
				$this->set($content);
			}
		}
		
		parent::renderView();
	}
	
	public function addControl($icon, $ui = null)
	{
		return $this->addAction($icon, 'applet-control ' . $ui);
	}
	
	public function addAction($icon, $ui = null)
	{
		$control = $this->add(['View', 'ui' => $ui], 'Controls')->link('#');
		$control->add(['Icon', $icon]);
		
		return $control;
	}
}
