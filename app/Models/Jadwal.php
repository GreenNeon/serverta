<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jadwal extends Model
{
	protected $table = 'jadwal';
	// protected $fillable = [];
	protected $guarded = [];
	protected $hidden = [];
	// protected $visible = [];
	protected $casts = ['mulai','berhenti'];
}
