<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\KegiatanCollection as RCKegiatan;
use App\Http\Resources\Kegiatan as RKegiatan;
use App\Models\Kegiatan;
use App\Helper\Traits\Table;
use App\Models\Jadwal;
use App\Models\Siswa;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class KegiatanController extends ApiBaseController
{
	use Table;

	public function __construct()
	{
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$query = Kegiatan::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like', '_with']);
		$with = ['jadwal'];
		try {
			$query = $this->TableWith($query, $settings['_with'] ?? [], $with);
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			if (isset($settings['_limit']) && $settings['_limit'] === 'inf') {
				return new RCKegiatan($query->get());
			} else {
				if (!empty($settings['_limit']) && !is_int($settings['_limit'])) $settings['_limit'] = 5;
				return new RCKegiatan($query->paginate($settings['_limit']));
			}
		} catch (QueryException $qe) {
			return $this->ResponseError([], self::QUERY_ID, $qe->getMessage());
		} catch (\Throwable $th) {
			return $this->ResponseError([], self::ANY_UNK, $th->getMessage());
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$input = $request->only([
			'title', 'tanggal', 'deskripsi'
		]);
		$validator = Validator::make($request->all(), [
			'title' => 'required',
			'tanggal' => 'required|date',
			'fk_foto' => 'nullable|exists:foto,foto_id'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$jadwal = Jadwal::create(['mulai' => $input['tanggal'], 'berhenti' => $input['tanggal']]);
			unset($input['tanggal']);
			$input['fk_jadwal'] = $jadwal->id;
			$kegiatan = Kegiatan::create($input);

			$foto = $request->fk_foto;
			if (!empty($foto)) $kegiatan->avatar()->attach($foto, ['nama' => 'avatar', 'deskripsi' => 'foto profil', 'role' => 'profile']);
			return new RKegiatan($kegiatan);
		} catch (\Throwable $th) {
			return $this->ResponseError([], self::QUERY_SV, $th->getMessage());
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		return new RKegiatan(Kegiatan::with('jadwal')->findOrFail($id));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		$input = $request->only([
			'title', 'tanggal', 'deskripsi'
		]);
		
		$validator = Validator::make($input, [
			'fk_siswa' => 'exists:siswa,id,deleted_at,NULL',
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$kegiatan = Kegiatan::with('jadwal')->findOrFail($id);
			$jadwal = Jadwal::create(['mulai' => $input['tanggal'], 'berhenti' => $input['tanggal']]);
			unset($input['tanggal']);
			if($jadwal->mulai != $kegiatan->jadwal->mulai) $kegiatan->fk_jadwal = $jadwal->id;
			$kegiatan->fill($input)->save();
			return new RKegiatan($kegiatan);
		} catch (\Throwable $th) {
			return $this->ResponseError([], self::QUERY_ED, $th->getMessage());
		}
	}

	/**
	 * Restore the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	// public function restore($id)
	// {
	// 	$kegiatan = Kegiatan::findOrFail($id);
	// 	$kegiatan->restore();
	// 	return new RKegiatan($kegiatan);
	// }

	/**
	 * Thrash the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	// public function destroy($id)
	// {
	// 	$kegiatan = Kegiatan::findOrFail($id);
	// 	$kegiatan->delete();
	// 	return new RKegiatan($kegiatan);
	// }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$kegiatan = Kegiatan::findOrFail($id);
		$kegiatan->forceDelete();
		return new RKegiatan($kegiatan);
	}
}
