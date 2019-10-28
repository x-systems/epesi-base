<?php 

namespace Epesi\Base\User\Settings\Integration\Joints;

use Epesi\Core\Integration\Module\ModuleJoint;
use Epesi\Core\Integration\Concerns\HasLaunchButton;
use Epesi\Core\Integration\Concerns\HasOptions;

abstract class UserSettingsJoint extends ModuleJoint
{
	use HasOptions;
	use HasLaunchButton;
	
	/**
	 * Define group under which the settings are saved
	 * 
	 * @var string
	 */
	protected $group;

	/**
	 * Get the group under which settings are saved
	 *
	 * @return string
	 */
	public function group()
	{
		return $this->group?: static::class;
	}
	
	/**
	 * Define the settings view
	 */
	public function link() {
		return ['user.settings', 'edit', static::class];
	}
}