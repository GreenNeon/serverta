<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\KelasCollection as RCKelas;
use App\Http\Resources\SiswaCollection as RCSiswa;
use App\Http\Resources\PembelajaranCollection as RCPembelajaran;
use App\Http\Resources\Kelas as RKelas;
use App\Http\Resources\Pegawai as RGuru;
use App\Http\Resources\Album as RAlbum;
use App\Models\Kelas;
use App\Helper\Traits\Table;
use App\Http\Resources\SiswaNilai;
use App\Models\Foto;
use App\Models\Jadwal;
use App\Models\Album;
use App\Models\Pembelajaran;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class KelasController extends ApiBaseController
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
		$query = Kelas::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like', '_with']);

		$with = ['siswa', 'pembelajaran', 'pegawai', 'jadwal'];
		try {
			$query = $this->TableWith($query, $settings['_with'] ?? [], $with);
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			$data = [];
			if (isset($settings['_limit']) && $settings['_limit'] === 'inf') {
				$data = $query->get();
			} else {
				if (!empty($settings['_limit']) && !is_int($settings['_limit'])) 		$settings['_limit'] = 5;
				$data = $query->paginate($settings['_limit']);
			}
			return new RCKelas($data);
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
		$query = Kelas::query();
		$settings = $request->only(['_like']);
		try {
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			return new RCKelas($query->orderBy('nama')->take(5)->get());
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
		$input = $request->only(['nama', 'kelompok', 'fk_pegawai', 'jadwal.mulai', 'jadwal.berhenti', 'fk_foto']);
		$validator = Validator::make($input, [
			'nama' => 'required',
			'kelompok' => 'required|in:A,B',
			'fk_pegawai' => 'nullable|exists:pegawai,id,deleted_at,NULL',
			'jadwal.mulai' => 'required|date',
			'jadwal.berhenti' => 'required|date',
			'fk_foto' => 'nullable|exists:foto,foto_id'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$jadwal = $request->jadwal;
			if (!empty($jadwal)) {
				$jadwal = Jadwal::create($jadwal);
				$input['fk_jadwal'] = $jadwal->id;
			}
			unset($input['jadwal']);
			unset($input['fk_foto']);
			$kelas = Kelas::create($input);
			$album = Album::create(['title' => "Album kelas {$input['nama']}", 'deskripsi' => 'album khusus kelas']);
			$kelas->album->attach($album->id);

			$foto = $request->fk_foto;
			if (!empty($foto)) $kelas->avatar()->attach($foto, ['nama' => 'avatar', 'deskripsi' => 'foto profil', 'role' => 'profile']);

			return new RKelas($kelas);
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
		return new RKelas(Kelas::with(['pegawai', 'jadwal'])->findOrFail($id));
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
		$input = $request->only(['nama', 'kelompok', 'fk_pegawai', 'jadwal.mulai', 'jadwal.berhenti', 'fk_foto']);
		$validator = Validator::make($input, [
			'fk_foto' => 'nullable|exists:foto,foto_id',
			'nama' => 'required',
			'kelompok' => 'required|in:A,B',
			'fk_pegawai' => 'nullable|exists:pegawai,id,deleted_at,NULL',
			'jadwal.mulai' => 'required|date',
			'jadwal.berhenti' => 'required|date'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$kelas = Kelas::with(['pegawai', 'jadwal'])->findOrFail($id);
			$jadwal = $input['jadwal'];
			unset($input['jadwal']);
			unset($input['fk_foto']);
			$kelas->fill($input)->save();

			if (!empty($jadwal)) {
				if (empty($kelas->jadwal)) {
					$m_jadwal = Jadwal::create($jadwal);
					$kelas->fk_jadwal = $m_jadwal->id;
					$kelas->save();
				} else $kelas->jadwal()->update($jadwal);
			}

			$foto = $request->fk_foto;
			if (!empty($foto)) $kelas->avatar()->attach($foto, ['nama' => 'avatar', 'deskripsi' => 'foto profil', 'role' => 'profile']);

			return new RKelas($kelas);
		} catch (\Throwable $th) {
			return $this->ResponseError($th->getMessage(), self::QUERY_ED);
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
	// 	$kelas = Kelas::findOrFail($id);
	// 	$kelas->restore();
	// 	return new RKelas($kelas);
	// }

	/**
	 * Thrash the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	// public function destroy($id)
	// {
	// 	$kelas = Kelas::findOrFail($id);
	// 	$kelas->delete();
	// 	return new RKelas($kelas);
	// }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$kelas = Kelas::findOrFail($id);
		$kelas->forceDelete();
		return new RKelas($kelas);
	}
	/**
	 * Link siswa with kelas
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function LinkSiswa(Request $request, $id)
	{
		$validator = Validator::make($request->only(['fk_siswa']), [
			'fk_siswa' => 'required|array',
			'fk_siswa.*' => 'numeric'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$kelas = Kelas::findOrFail($id);
		$kelas->siswa()->attach($request->post('fk_siswa'));

		return response()->json(['message' => 'Berhasil menyambungkan siswa!'], 200);
	}

	/**
	 * Unlink siswa with kelas
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function UnLinkSiswa(Request $request, $id)
	{
		$validator = Validator::make($request->only(['fk_siswa']), [
			'fk_siswa' => 'required|array',
			'fk_siswa.*' => 'numeric'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$kelas = Kelas::findOrFail($id);
		$kelas->siswa()->detach($request->post('fk_siswa'));

		return response()->json(['message' => 'Berhasil memutuskan siswa!'], 200);
	}

	/**
	 * Link pembelajaran with kelas
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function LinkPembelajaran(Request $request, $id_kelas)
	{
		$validator = Validator::make($request->only(['id', 'tanggal']), [
			'id' => 'array',
			'tanggal' => 'array',
			'id.*' => 'numeric',
			'tanggal.*' => 'date'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$ids = $request->input('id');
		$opts = $request->input('tanggal');
		$newLinks = [];

		$count = Kelas::findOrFail($id_kelas)->pembelajaran()->whereIn('fk_pembelajaran', $ids)->whereIn('tanggal', $opts)->count();
		if($count >= 4) abort(404);

		foreach ($ids as $key => $id) {
			$newLinks[$id] = ['tanggal' => $opts[$key]];
		}

		$kelas = Kelas::findOrFail($id_kelas);		
		$kelas->pembelajaran()->attach($newLinks);

		return response()->json(['message' => 'Berhasil menyambungkan pembelajaran!'], 200);
	}

	/**
	 * Unlink pembelajaran with kelas
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function UnLinkPembelajaran(Request $request, $id_kelas)
	{
		$validator = Validator::make($request->only(['fk_pembelajaran']), [
			'fk_pembelajaran' => 'required|array',
			'fk_pembelajaran.*' => 'numeric'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$kelas = Kelas::findOrFail($id_kelas);
		$kelas->pembelajaran()->detach($request->post('fk_pembelajaran'));

		return response()->json(['message' => 'Berhasil memutuskan pembelajaran!'], 200);
	}

	/**
	 * Link foto with siswa
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

		$siswa = Kelas::findOrFail($id);

		$path = $request->file('foto')->store(
			'avatars',
			'photos'
		);

		$image = $siswa->avatar->first();
		if (!empty($image)) {
			Storage::disk('photos')->delete('avatars/' . $image->file_path);
			$siswa->avatar()->detach();
		}

		try {
			$foto = Foto::create(['file_path' => $path]);

			$siswa->avatar()->attach($foto->foto_id, ['nama' => 'avatar', 'deskripsi' => 'foto profil', 'role' => 'profile']);
			$base_url = url('/');
			return response()->json(['data' => ['id' => $foto->foto_id, 'profile_url' => "{$base_url}/api/photos/{$foto->foto_id}"], 'message' => 'Berhasil menambahkan foto!'], 200);
		} catch (\Throwable $th) {
			throw $th;
			return $this->ResponseError($validator->errors(), self::ANY_PAR);
		}
	}

	/**
	 * Link foto with album
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function UploadAlbum(Request $request, $id)
	{
		$validator = Validator::make($request->only(['foto']), [
			'foto' => 'image|max:2048'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$kelas = Kelas::findOrFail($id);
		$path = $request->file('foto')->store(
			"albums/{$kelas->id}",
			'photos'
		);

		try {
			$foto = Foto::create(['file_path' => $path]);

			$base_url = url('/');
			$kelas->album->photos()->attach($foto->foto_id, ['nama' => "Album Kelas {$kelas->nama}", 'deskripsi' => 'foto album kelas', 'role' => 'album']);
			return response()->json(['data' => ['id' => $foto->foto_id, 'profile_url' => "{$base_url}/api/photos/{$foto->foto_id}"], 'message' => 'Berhasil menambahkan foto!'], 200);
		} catch (\Throwable $th) {
			throw $th;
			return $this->ResponseError($validator->errors(), self::ANY_PAR);
		}
	}

	/**
	 * Unlink foto with kelas
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function UnLinkFoto(Request $request, $id)
	{
		$validator = Validator::make($request->only(['fk_foto']), [
			'fk_foto' => 'required|array',
			'fk_foto.*' => 'numeric'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$kelas = Kelas::findOrFail($id);
		$kelas->album->photos()->detach($request->post('fk_foto'));

		return response()->json(['message' => 'Berhasil memutuskan foto!'], 200);
	}

	public function ShowGuru($id)
	{
		$kelas = Kelas::with('pegawai')->findOrFail($id);
		return new RGuru($kelas->pegawai);
	}

	public function ShowAlbum($id)
	{
		$kelas = Kelas::findOrFail($id);
		$album = $kelas->album;
		if (empty($album)) {
			$album = Album::create([
				'title' => "Album kelas {$kelas->nama}",
				'deskripsi' => 'album khusus kelas',
				'fk_kelas' => $kelas->id
			]);
		}

		return new RAlbum($kelas->album);
	}

	public function ShowPembelajaran($id)
	{
		$kelas = Kelas::with(['pembelajaran', 'pembelajaran.indikator'])->findOrFail($id);
		$pembelajaran = $kelas->pembelajaran;
		$pembelajaran = $pembelajaran->sortBy('pivot.tanggal')->values()->all();
		return new RCPembelajaran($pembelajaran);
	}

	public function ShowSiswa(Request $request, $id)
	{
		$kelas = Kelas::findOrFail($id);
		$query = $kelas->siswa();
		$settings = $request->only(['page', '_limit', '_filter', '_like', '_with']);

		$with = ['kelas', 'orangtua'];
		try {
			$query = $this->TableWith($query, $settings['_with'] ?? [], $with);
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			// dump(DB::getQueryLog()); // Show results of log
			$data = [];
			if (isset($settings['_limit']) && $settings['_limit'] === 'inf') {
				$data = $query->get();
			} else {
				if (empty($settings['_limit']) || !is_int((int) $settings['_limit']))
					$settings['_limit'] = 5;
				$data = $query->paginate($settings['_limit']);
			}
			// dd(DB::getQueryLog(), $settings); // Show results of log
			return new RCSiswa($data);
		} catch (QueryException $qe) {
			return $this->ResponseError([], self::QUERY_ID, $qe->getMessage());
		} catch (\Throwable $th) {
			return $this->ResponseError([], self::ANY_UNK, $th->getMessage());
		}
	}

	public function ShowNilai($id) {
		$kelas = Kelas::with('siswa')->findOrFail($id);
		$siswa_s = $kelas->siswa;
		$nilai_s = $siswa_s->map(
			function ($item, $key) {
				return new SiswaNilai($item);
			}
		);
		return response()->json(['data' => $nilai_s], 200);
	}
}
