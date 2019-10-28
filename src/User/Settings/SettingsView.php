<?php

namespace Epesi\Base\User\Settings;

use Epesi\Core\Integration\Module\ModuleView;
use Epesi\Base\User\Settings\Integration\Joints\UserSettingsJoint;
use Epesi\Core\UI\Seeds\Form;
use Epesi\Core\UI\Seeds\ActionBar;
use Epesi\Base\User\Settings\Database\Models\UserSetting;

class SettingsView extends ModuleView
{
	protected $label = 'User Settings';
	
	public function body()
	{
		$layout = $this->add(['View'])->addStyle('max-width:800px;margin:auto;');
		$layout->add(['Header', __('User Settings')]);
		$segment = $layout->add(['View', ['ui' => 'segment']]);
		
		foreach (UserSettingsJoint::collect() as $joint) {
			$segment->add($joint->button());
		}
	}
	
	public function edit($jointClass)
	{
		$joint = new $jointClass();
		
		$this->location($joint->label());
		
		$form = $this->add(new Form())
			->addElements($joint->elements())
			->confirmLeave()
			->setValues(UserSetting::getGroup($joint->group()));

		$form->validate(function(Form $form) use ($joint) {
			UserSetting::putGroup($joint->group(), $form->getValues());
			
			return $form->notify(__('Settings saved!'));
		});
		
		ActionBar::addButton('back');
			
		ActionBar::addButton('save')->on('click', $form->submit());
	}
}
