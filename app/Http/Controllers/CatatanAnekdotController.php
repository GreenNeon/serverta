<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\CatatanAnekdotCollection as RCCatatanAnekdot;
use App\Http\Resources\CatatanAnekdot as RCatatanAnekdot;
use App\Models\CatatanAnekdot;
use App\Helper\Traits\Table;
use App\Models\Siswa;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class CatatanAnekdotController extends ApiBaseController
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
		$query = CatatanAnekdot::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like', '_with']);

		$with = ['siswa', 'kelas'];
		try {
			$query = $this->TableWith($query, $settings['_with'] ?? [], $with);
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			if (isset($settings['_limit']) && $settings['_limit'] === 'inf') {
				return new RCCatatanAnekdot($query->get());
			} else {
				if (!empty($settings['_limit']) && !is_int($settings['_limit'])) $settings['_limit'] = 5;
				return new RCCatatanAnekdot($query->paginate($settings['_limit']));
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
			'fk_siswa', 'tanggal', 'peristiwa', 'evaluasi', 'keterangan'
		]);
		$validator = Validator::make($request->all(), [
			'peristiwa' => 'required',
			'tanggal' => 'required|date',
			'fk_siswa' => 'required|exists:siswa,id',
			'fk_foto' => 'nullable|exists:foto,foto_id'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$siswa = Siswa::findOrFail($input['fk_siswa']);
		$kelas = $siswa->kelasByStatus->sortByDesc('jadwal.berhenti');
		if (empty($kelas)) {
			$kelas = $siswa->kelasByLast();
			if (empty($kelas)) return $this->ResponseError([], self::QUERY_SV, 'Tidak memiliki kelas ..');
		}

		try {
			$input['fk_kelas'] = $kelas->first()->id;
			$catatananekdot = CatatanAnekdot::create($input);

			$foto = $request->fk_foto;
			if (!empty($foto)) $catatananekdot->avatar()->attach($foto, ['nama' => 'avatar', 'deskripsi' => 'foto profil', 'role' => 'profile']);
			return new RCatatanAnekdot($catatananekdot);
		} catch (\Throwable $th) {
			return $this->ResponseError([], self::QUERY_SV);
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
		return new RCatatanAnekdot(CatatanAnekdot::with(['siswa', 'kelas'])->findOrFail($id));
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
			'fk_siswa', 'tanggal', 'peristiwa', 'evaluasi', 'keterangan'
		]);
		
		$validator = Validator::make($input, [
			'fk_siswa' => 'exists:siswa,id,deleted_at,NULL',
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$catatananekdot = CatatanAnekdot::findOrFail($id);
			$catatananekdot->fill($input)->save();
			return new RCatatanAnekdot($catatananekdot);
		} catch (\Throwable $th) {
			return $this->ResponseError([], self::QUERY_ED);
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
	// 	$catatananekdot = CatatanAnekdot::findOrFail($id);
	// 	$catatananekdot->restore();
	// 	return new RCatatanAnekdot($catatananekdot);
	// }

	/**
	 * Thrash the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	// public function destroy($id)
	// {
	// 	$catatananekdot = CatatanAnekdot::findOrFail($id);
	// 	$catatananekdot->delete();
	// 	return new RCatatanAnekdot($catatananekdot);
	// }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$catatananekdot = Catatananekdot::findOrFail($id);
		$catatananekdot->forceDelete();
		return new RCatatananekdot($catatananekdot);
	}
}
