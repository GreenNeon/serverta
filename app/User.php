<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
	use Notifiable;

	protected $table = 'user';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [];

	public function pegawai()
	{
		return $this->hasOne('App\Models\Pegawai', 'id', 'fk_pegawai');
	}

	public function siswa()
	{
		return $this->hasOne('App\Models\Siswa', 'id', 'fk_siswa');
	}

	public function GetRole()
	{
		$pegawai = $this->pegawai;

		$role = 'OR';
		if (!empty($pegawai)) $role = $pegawai->roles;
		return $role;
	}
	/**
	 * Get the identifier that will be stored in the subject claim of the JWT.
	 *
	 * @return mixed
	 */
	public function getJWTIdentifier()
	{
		return $this->getKey();
	}

	/**
	 * Return a key value array, containing any custom claims to be added to the JWT.
	 *
	 * @return array
	 */
	public function getJWTCustomClaims()
	{
		$id = $this->fk_pegawai ?? $this->fk_siswa;
		return [
			"id" => $id,
			"username" => $this->username,
			"role" => $this->GetRole()
		];
	}
}
