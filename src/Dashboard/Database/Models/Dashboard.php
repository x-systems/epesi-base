<?php

namespace Epesi\Base\Dashboard\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dashboard extends Model
{
	public $timestamps = false;
	protected $fillable = ['user_id', 'name', 'position'];

	/**
	 * A dashboard has many applets.
	 *
	 * @return HasMany
	 */
	public function applets() : HasMany
	{
		return $this->hasMany(DashboardApplet::class, 'dashboard_id');
	}
	
	public function delete()
	{
		$this->applets()->delete();
		
		return parent::delete();
	}
}
