<?php

namespace Epesi\Base\Dashboard\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardApplet extends Model
{
	public $timestamps = false;
	protected static $unguarded = true;
	
	protected $casts = [
			'options' => 'array'
	];
	
	/**
	 * An applet belongs to one dashboard
	 *
	 * @return BelongsTo
	 */
	public function dashboard() : BelongsTo
	{
		return $this->belongsTo(Dashboard::class);
	}
}
