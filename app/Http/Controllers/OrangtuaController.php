<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\OrangtuaCollection as RCOrangtua;
use App\Http\Resources\SiswaCollection as RCSiswa;
use App\Http\Resources\Orangtua as ROrangtua;
use App\Models\Orangtua;
use App\Helper\Traits\Table;
use App\Models\Alamat;
use App\Models\Foto;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OrangtuaController extends ApiBaseController
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
		$query = Orangtua::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like']);
		try {
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);
			
			if (isset($settings['_limit']) && $settings['_limit'] === 'inf') {
				return new RCOrangtua($query->get());
			} else {
				if (empty($settings['_limit']) || !is_int((int)$settings['_limit'])) $settings['_limit'] = 5;
				return new RCOrangtua($query->paginate($settings['_limit']));
			}
		} catch (QueryException $qe) {
			return $this->ResponseError([], self::QUERY_ID, $qe->getMessage());
		} catch (\Throwable $th) {
			return $this->ResponseError([], self::ANY_UNK, $th->getMessage());
		}
	}

	/**
	 * Display a listing of the resource.
	 * for search
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function search(Request $request)
	{
		$query = Orangtua::query();
		$settings = $request->only(['_like']);
		try {
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			return new RCOrangtua($query->orderBy('nama')->take(5)->get());
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
			'nama', 'nik', 'gender', 'tanggal_lahir', 'pendidikan', 'pekerjaan', 'penghasilan', 'kebutuhan_khusus', 'telepon', 'smartphone', 'email', 'wali', 'fk_foto'
		]);
		$alamat = $request->only(['alamat.provinsi', 'alamat.kabupaten', 'alamat.kecamatan', 'alamat.kelurahan', 'alamat.alamat', 'alamat.rt', 'alamat.rw']);

		$validator = Validator::make($input, [
			'fk_foto' => 'nullable|exists:foto,foto_id',
			'nik' => 'required|unique:orangtua',
			'wali' => 'required|boolean',
			'nama' => 'required',
			'gender' => 'required|in:L,P',
			'smartphone' => 'required',
			'email' => 'nullable|email',
			'telepon' => 'nullable',
			'tanggal_lahir' => 'required|date',
			'penghasilan' => 'nullable',
			'kebutuhan_khusus' => 'nullable'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			unset($input['fk_foto']);
			$ortu = Orangtua::create($input);
			if (!empty($alamat)) {
				$alamat = $alamat['alamat'];
				$m_alamat = Alamat::create($alamat);
				$ortu->fk_alamat = $m_alamat->id;
				$ortu->save();
			}
			$foto = $request->fk_foto;
			if (!empty($foto)) $ortu->avatar()->attach($foto, ['nama' => 'avatar', 'deskripsi' => 'foto profil', 'role' => 'profile']);

			return new ROrangtua($ortu);
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
		return new ROrangtua(Orangtua::with('alamat')->findOrFail($id));
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
			'nama', 'nik', 'gender', 'tanggal_lahir', 'pendidikan', 'pekerjaan', 'penghasilan', 'kebutuhan_khusus', 'telepon', 'smartphone', 'email', 'wali', 'fk_foto'
		]);
		$alamat = $request->only(['alamat.provinsi', 'alamat.kabupaten', 'alamat.kecamatan', 'alamat.kelurahan', 'alamat.alamat', 'alamat.rt', 'alamat.rw']);

		$validator = Validator::make($input, [
			'fk_foto' => 'nullable|exists:foto,foto_id',
			'nik' => 'unique:orangtua,nik,'.$id,
			'wali' => 'boolean',
			'gender' => 'in:L,P',
			'email' => 'nullable|email',
			'telepon' => 'nullable',
			'tanggal_lahir' => 'date',
			'penghasilan' => 'nullable',
			'kebutuhan_khusus' => 'nullable',
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$ortu = Orangtua::with('alamat')->findOrFail($id);
			unset($input['fk_foto']);
			$ortu->fill($input)->save();
			if (!empty($alamat)) {
				$alamat = $alamat['alamat']; 
				unset($alamat['id']);
				if (empty($ortu->alamat)) {
					$m_alamat = Alamat::create($alamat);
					$ortu->fk_alamat = $m_alamat->id;
					$ortu->save();
				} else $ortu->alamat()->update($alamat);
			}
			$foto = $request->fk_foto;
			if (!empty($foto)) $ortu->avatar()->attach($foto, ['nama' => 'avatar', 'deskripsi' => 'foto profil', 'role' => 'profile']);

			return new ROrangtua($ortu);
		} catch (\Throwable $th) {
			throw $th;
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
	// 	$ortu = Orangtua::findOrFail($id);
	// 	$ortu->restore();
	// 	return new ROrangtua($ortu);
	// }

	/**
	 * Thrash the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	// public function destroy($id)
	// {
	// 	$ortu = Orangtua::findOrFail($id);
	// 	$ortu->delete();
	// 	return new ROrangtua($ortu);
	// }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, $id)
	{
		if (!empty($request->id)) {
			$id = $request->id;
			$orangtua = Orangtua::whereIn('id',$id)->forceDelete();
		} else {
			$orangtua = Orangtua::findOrFail($id);
			$orangtua->forceDelete();
		}
		return response()->json(['data' => $orangtua,'message' => 'Berhasil menghapus orang tua!'], 200);
	}

	/**
	 * Link siswa with orangtua
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function siswa(Request $request, $id)
	{
		$validator = Validator::make($request->only(['fk_siswa']), [
			'fk_siswa' => 'nullable|array',
			'fk_siswa.*' => 'numeric'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$siswa = Orangtua::findOrFail($id);
		$siswa->siswa()->attach($request->post('fk_siswa'));

		return response()->json(['message' => 'Berhasil menyambungkan siswa!'], 200);
	}

	/**
	 * Unlink siswa with siswa
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function Unsiswa(Request $request, $id)
	{
		$validator = Validator::make($request->only(['fk_siswa']), [
			'fk_siswa' => 'nullable|array',
			'fk_siswa.*' => 'numeric'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$siswa = Orangtua::findOrFail($id);
		$siswa->siswa()->detach($request->post('fk_siswa'));

		return response()->json(['message' => 'Berhasil memutuskan siswa!'], 200);
	}

	/**
	 * Link foto with orangtua
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function UploadProfil(Request $request, $id)
	{
		$validator = Validator::make($request->only(['foto']), [
			'foto' => 'image|max:512'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$pegawai = Orangtua::findOrFail($id);

		$path = $request->file('foto')->store(
			'avatars',
			'photos'
		);

		$image = $pegawai->avatar->first();
		if (!empty($image)) {
			Storage::disk('photos')->delete('avatars/' . $image->file_path);
			$pegawai->avatar()->detach();
		}

		try {
			$foto = Foto::create(['file_path' => $path]);

			$pegawai->avatar()->attach($foto->foto_id, ['nama' => 'avatar', 'deskripsi' => 'foto profil', 'role' => 'profile']);
			$base_url = url('/');
			return response()->json(['data' => ['profile_url' => "{$base_url}/api/photos/{$foto->foto_id}"], 'message' => 'Berhasil menambahkan foto!'], 200);
		} catch (\Throwable $th) {
			throw $th;
			return $this->ResponseError($validator->errors(), self::ANY_PAR);
		}
	}

	public function ShowSiswa($id)
	{
		$ortu = Orangtua::findOrFail($id);
		return new RCSiswa($ortu->siswa);
	}

	public function ReportOrangtua() {
		$ortuPekerjaan = [];
		$ortuAlamat = [];
		$array = Orangtua::with('alamat')->get();
		$group = $array->groupBy('pekerjaan')->toArray();
		$ortuPekerjaan = array_merge($ortuPekerjaan, ...array_map(function ($data, $key) {
			return [$key => sizeof($data)];
		}, $group, array_keys($group)));

		$group = $array->groupBy('alamat.kecamatan')->toArray();
		$ortuAlamat = array_merge($ortuAlamat, ...array_map(function ($data, $key) {
			return [$key => sizeof($data)];
		}, $group, array_keys($group)));
		return [
			'pekerjaan' => $ortuPekerjaan,
			'alamat' => $ortuAlamat
		];
	}
}
