<?php 

namespace Epesi\Base\Dashboard\Integration;

use Epesi\Base\User\Settings\Integration\Joints\UserSettingsJoint;
use Epesi\Base\Dashboard\DashboardCore;

class DashboardUserSettings extends UserSettingsJoint
{
	public function group()
	{
		return DashboardCore::alias();
	}
	
	public function label()
	{
		return __('Misc');
	}

	public function icon()
	{
		return 'list';
	}

	public function elements() {
		return [
				[
						'name' => 'skin',
						'decorator' => [
								'DropDown',
								'caption' => __('Clock skin'),
								'values' => [
										'swissRail' => 'swissRail',
										'chunkySwiss' => 'chunkySwiss',
										'chunkySwissOnBlack' => 'chunkySwissOnBlack',
										'fancy' => 'fancy',
										'machine' => 'machine',
										'classic' => 'classic',
										'modern' => 'modern',
										'simple' => 'simple',
										'securephp' => 'securephp',
										'Tes2' => 'Tes2',
										'Lev' => 'Lev',
										'Sand' => 'Sand',
										'Sun' => 'Sun',
										'Tor' => 'Tor',
										'Babosa' => 'Babosa',
										'Tumb' => 'Tumb',
										'Stone' => 'Stone',
										'Disc' => 'Disc',
										'flash' => 'flash'
								]
						],
						'default' => 'swissRail',
						'rules' => [
								[
										'type' => 'empty',
										'prompt' => __('Field required'),										
								]
						],
						
				],
				[
						'name' => 'type',
						'decorator' => [
								'DropDown',
								'caption' => __('Type'),
								'values' => [
										'single' => __('Single Clock'),
										'double' => __('Double Clock')
								],
						],
						'default' => 'double',
				],
				[
						'name' => 'second_clock_label',
						'decorator' => [
								'caption' => __('Second clock label'),
						],
						'default' => __('Singapore / China'),
						'display' => ['type' => 'isExactly[double]']
				],
		];
	}
}