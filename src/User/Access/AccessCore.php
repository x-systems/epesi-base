<?php

namespace Epesi\Base\User\Access;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Epesi\Core\Integration\ModuleCore;
use Epesi\Base\User\Access\Integration\UserAccessSystemSettings;
use Epesi\Base\User\Access\Providers\UserAccessServiceProvider;

class AccessCore extends ModuleCore
{
	protected static $alias = 'user.access';
	
	protected static $joints = [
			UserAccessSystemSettings::class
	];
	
	protected static $providers = [
			UserAccessServiceProvider::class
	];
	
	public function defaultRoles()
	{
		return [
				'Super Admin',
				'Admin',
				'Employee',
				'Guest'
		];
	}
	
	public function install()
	{
		foreach ($this->defaultRoles() as $roleName) {
			Role::create(['name' => $roleName]);
		}
		
		Permission::create(['name' => 'modify system']);
		
		$modifySystemSettings = Permission::create(['name' => 'modify system settings']);
		
		Role::findByName('Admin')->givePermissionTo($modifySystemSettings);
	}

	public function uninstall()
	{
		Role::whereIn('name', $this->defaultRoles())->delete();
		
		Permission::findByName('modify system')->delete();
		Permission::findByName('modify system settings')->delete();
	}
}
