<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\PegawaiCollection as RCPegawai;
use App\Http\Resources\Pegawai as RPegawai;
use App\Models\Pegawai;
use App\Helper\Traits\Table;
use App\Models\Alamat;
use App\Models\Foto;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PegawaiController extends ApiBaseController
{
	use Table;

	public function __construct()
	{
		$this->middleware('auth:api');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$query = Pegawai::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like']);
		try {
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			if (isset($settings['_limit']) && $settings['_limit'] === 'inf') {
				return new RCPegawai($query->get());
			} else {
				if (empty($settings['_limit']) || !is_int((int)$settings['_limit'])) $settings['_limit'] = 5;
				return new RCPegawai($query->paginate($settings['_limit']));
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
		$query = Pegawai::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like']);

		try {
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			return new RCPegawai($query->orderBy('nama')->take(5)->get());
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
			'nama', 'nik', 'role', 'gender', 'tanggal_lahir', 'telepon', 'smartphone', 'email', 'fk_foto'
		]);
		$alamat = $request->only(['alamat.provinsi', 'alamat.kabupaten', 'alamat.kecamatan', 'alamat.kelurahan', 'alamat.alamat', 'alamat.rt', 'alamat.rw']);

		$validator = Validator::make($input, [
			'fk_foto' => 'nullable|exists:foto,foto_id',
			'nik' => 'required|unique:pegawai',
			'nama' => 'required',
			'role' => 'required|in:SA,PG,GU,OR',
			'gender' => 'required|in:L,P',
			'smartphone' => 'required',
			'telepon' => 'nullable',
			'email' => 'nullable|email',
			'tanggal_lahir' => 'required|date'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$now = Carbon::now();
		$max = Pegawai::max('id') || 0;
		$input['nip'] = ((string)random_int(100,500)). '.' .str_split(((string) $now->year), 2)[1]  . str_pad(((string) $now->month), 2, '0', STR_PAD_LEFT) . '.' . str_pad(((string) $max), 3, '0', STR_PAD_LEFT);

		try {
			unset($input['fk_foto']);
			$pegawai = Pegawai::create($input);
			if (!empty($alamat)) {
				$alamat = $alamat['alamat'];
				$m_alamat = Alamat::create($alamat);
				$pegawai->fk_alamat = $m_alamat->id;
				$pegawai->save();
			}
			
			$foto = $request->fk_foto;
			if (!empty($foto)) $pegawai->avatar()->attach($foto, ['nama' => 'avatar', 'deskripsi' => 'foto profil', 'role' => 'profile']);
			$this->ResetPassword($pegawai->id);
			return new RPegawai($pegawai);
		} catch (\Throwable $th) {
			throw $th;
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
		return new RPegawai(Pegawai::with('alamat')->findOrFail($id));
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
			'nama', 'nik', 'role', 'gender', 'tanggal_lahir', 'telepon', 'smartphone', 'email', 'fk_foto'
		]);
		$alamat = $request->only(['alamat.provinsi', 'alamat.kabupaten', 'alamat.kecamatan', 'alamat.kelurahan', 'alamat.alamat', 'alamat.rt', 'alamat.rw']);

		$validator = Validator::make($input, [
			'fk_foto' => 'nullable|exists:foto,foto_id',
			'nik' => 'required|unique:pegawai,nik,'.$id,
			'nama' => 'required',
			'role' => 'required|in:PG,GU,OR',
			'gender' => 'required|in:L,P',
			'smartphone' => 'required',
			'telepon' => 'nullable',
			'email' => 'nullable|email',
			'tanggal_lahir' => 'required|date'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$pegawai = Pegawai::with('alamat')->findOrFail($id);
			unset($input['fk_foto']);
			$pegawai->fill($input)->save();
			if (!empty($alamat)) {
				$alamat = $alamat['alamat']; 
				unset($alamat['id']);
				if (empty($pegawai->alamat)) {
					$m_alamat = Alamat::create($alamat);
					$pegawai->fk_alamat = $m_alamat->id;
					$pegawai->save();
				} else $pegawai->alamat()->update($alamat);
			}
			
			$foto = $request->fk_foto;
			if (!empty($foto)) $pegawai->avatar()->attach($foto, ['nama' => 'avatar', 'deskripsi' => 'foto profil', 'role' => 'profile']);

			return new RPegawai($pegawai);
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
	// 	$pegawai = Pegawai::findOrFail($id);
	// 	$pegawai->restore();
	// 	return new RPegawai($pegawai);
	// }

	/**
	 * Thrash the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	// public function destroy($id)
	// {
	// 	$pegawai = Pegawai::findOrFail($id);
	// 	$pegawai->delete();
	// 	return new RPegawai($pegawai);
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
			$pegawai = Pegawai::whereIn('id',$id)->forceDelete();
		} else {
			$pegawai = Pegawai::findOrFail($id);
			$pegawai->forceDelete();
		}
		return response()->json(['data' => $pegawai,'message' => 'Berhasil menghapus pegawai!'], 200);
	}

	/**
	 * Link foto with pegawai
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

		$pegawai = Pegawai::findOrFail($id);

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

	public function ResetPassword($id) {
		$pegawai = Pegawai::findOrFail($id);
		$user = $pegawai->user()->updateOrCreate(
			["fk_pegawai" => $id],
			[
				"username" => $pegawai->nip,
				"password" => bcrypt($pegawai->nip),
				"fk_pegawai" => $pegawai->id
			]
		);
		return response()->json($user, 200);
	}
}
