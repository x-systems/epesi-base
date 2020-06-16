<?php

namespace Epesi\Base\Dashboard\View;

use atk4\ui\View;
use Epesi\Core\System\View\Form;
use Epesi\Base\Dashboard\Models\DashboardApplet;
use atk4\ui\Modal;
use atk4\ui\Button;

class Applet extends View
{
	public $ui = 'applet segment raised';
	public $defaultTemplate = 'applet.html';
	
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
			
			$modal = Modal::addTo($this, ['title' => __('Edit :applet Applet Settings', ['applet' => $joint->caption()])]);
			
			$modal->set(function ($canvas) use ($modal, $joint) {
			    $form = Form::addTo($canvas, ['buttonSave' => [Button::class, __('Save'), 'primary']]);
			    
			    $form->addElements($joint->elements());
			    $form->confirmLeave();
			    
			    $form->model->set($this->options);
			    
			    $form->validate(function(Form $form) use ($modal, $joint) {
			        $model = DashboardApplet::create();
			        $model->id = $this->appletId;
			        $model->save(['options' => $form->model->get()]);
			        
			        return [
			            $modal->hide(),
			            $this->jsReload(),
			            $form->notifySuccess(__('Settings for :applet applet saved!', ['applet' => $joint->caption()]))			            
			        ];
			    });
			    
			    Button::addTo($form, __('Cancel'))->on('click', $modal->hide());
			});
			
			$this->addControl('ellipsis vertical')->setAttr('title', __('Applet settings'))->on('click', $modal->show());
			
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
