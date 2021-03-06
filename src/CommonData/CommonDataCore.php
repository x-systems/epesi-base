<?php

namespace Epesi\Base\CommonData;

use Epesi\Core\System\Modules\ModuleCore;

class CommonDataCore extends ModuleCore
{
	protected static $alias = 'commondata';
	
	protected static $view = CommonDataSettings::class;
	
	protected static $joints = [
			Integration\CommonDataSystemSettings::class,
	];
	
	public static function info()
	{
		return [
				__('Author') => 'Georgi Hristov',
				__('Copyright') => 'X Systems Ltd',
				'',
				'Provides commondata lists functionality'
		];
	}
	
	public function install()
	{
	    Models\CommonData::migrate();
	}
	
	public function uninstall()
	{
	    
	}
}
