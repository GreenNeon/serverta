<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
	protected $table = 'blog';
	// protected $fillable = [];
	protected $guarded = [];
	protected $hidden = [];
	// protected $visible = [];
	// protected $casts = [];

	public function image()
	{
		return $this->morphToMany('App\Models\Foto', 'resource', 'resources', 'resource_id', 'id', 'id', 'foto_id')->withPivot('nama', 'deskripsi', 'role')->wherePivot('role', 'post');
	}

	/**
	 * Get the event
	 *
	 * @return bool
	 */
	public function getLightModeAttribute($value)
	{
		return $value;
	}
	
	/**
	 * Set event
	 *
	 * @param  string  $value
	 * @return void
	 */
	public function setLightModeAttribute($value)
	{
		$this->attributes['LightMode'] = $value;
	}
}

