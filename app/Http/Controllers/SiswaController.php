<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\SiswaCollection as RCSiswa;
use App\Http\Resources\JadwalCollection as RCJadwal;
use App\Http\Resources\KelasCollection as RCKelas;
use App\Http\Resources\OrangtuaCollection as RCOrangtua;
use App\Http\Resources\NilaiCollection as RCNilai;
use App\Http\Resources\CatatanAnekdotCollection as RCCatatan;
use App\Http\Resources\TinggiBeratCollection as RCTinggiBerat;
use App\Http\Resources\Siswa as RSiswa;
use App\Models\Siswa;
use App\Helper\Traits\Table;
use App\Models\Foto;
use App\Models\Indikator;
use App\Models\Kegiatan;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\Orangtua;
use App\Models\TinggiBerat;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SiswaController extends ApiBaseController
{
	use Table;

	public function __construct()
	{
		$this->middleware('auth:api')->except('ReportTahun');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$query = Siswa::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like', '_with']);

		$with = ['kelas', 'orangtua'];
		try {
			$query = $this->TableWith($query, $settings['_with'] ?? [], $with);
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			$data = [];
			if (isset($settings['_limit']) && $settings['_limit'] === 'inf') {
				$data = $query->get();
			} else {
				if (empty($settings['_limit']) || !is_int((int) $settings['_limit']))
					$settings['_limit'] = 5;
				$data = $query->paginate($settings['_limit']);
			}
			return new RCSiswa($data);
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
		$query = Siswa::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like', '_with']);

		$with = ["kelas"];
		try {
			$query = $this->TableWith($query, $settings['_with'] ?? [], $with);
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			return new RCSiswa($query->orderBy('nama')->take(5)->get());
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
			'nama', 'gender', 'nisn', 'nik', 'tempat_lahir', 'tanggal_lahir', 'agama', 'kewarganegaraan', 'penyakit_berat', 'golongan_darah', 'kebutuhan_khusus', 'transportasi', 'anak_ke', 'jumlah_saudara', 'no_kps', 'no_kip', 'no_kks', 'reg_akta', 'fk_foto'
		]);

		$validator = Validator::make($input, [
			'fk_foto' => 'nullable|exists:foto,foto_id',
			'nisn' => 'required|unique:siswa',
			'nik' => 'required',
			'nama' => 'required',
			'kewarganegaraan' => 'required',
			'kebutuhan_khusus' => 'nullable',
			'agama' => 'required',
			'gender' => 'required|in:L,P',
			'tanggal_lahir' => 'required|date',
			'tempat_lahir' => 'required',
			'jumlah_saudara' => 'required|numeric',
			'anak_ke' => 'required|numeric',
			'penyakit_berat' => 'nullable',
			'transportasi' => 'required',
			'golongan_darah' => 'required|in:A,B,AB,O',
			'no_kps' => 'nullable|numeric',
			'no_kip' => 'nullable|numeric',
			'no_kks' => 'nullable|numeric',
			'reg_akta' => 'nullable|numeric',
			'fk_orangtua' => 'nullable|array',
			'fk_orangtua.*.tinggal_bersama' => 'required_if:fk_orangtua|boolean',
			'fk_tinggi_berat' => 'nullable|array',
			'fk_tinggi_berat.*' => 'numeric',
			'fk_kelas' => 'nullable|array',
			'fk_kelas.*' => 'numeric'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$now = Carbon::now();
		$max = Siswa::max('id') || 0;
		$input['nis'] = ((string)random_int(100, 500)) . '.' . str_split(((string) $now->year), 2)[1] . str_pad(((string) $now->month), 2, '0', STR_PAD_LEFT) . '.' . str_pad(((string) $max), 3, '0', STR_PAD_LEFT);

		try {
			unset($input['fk_foto']);
			$siswa = Siswa::create($input);
			if (!empty($request->post('fk_orangtua'))) {
				$siswa->orangtua()->sync($request->post('fk_orangtua'));
			}
			if (!empty($request->post('fk_tinggi_berat')))
				$siswa->tinggiberat()->sync($request->post('fk_tinggi_berat'));
			if (!empty($request->post('fk_kelas')))
				$siswa->kelas()->sync($request->post('fk_kelas'));

			$foto = $request->fk_foto;
			if (!empty($foto)) $siswa->avatar()->attach($foto, ['nama' => 'avatar', 'deskripsi' => 'foto profil', 'role' => 'profile']);
			$this->ResetPassword($siswa->id);
			return new RSiswa($siswa);
		} catch (\Throwable $th) {
			throw $th;
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
		return new RSiswa(Siswa::findOrFail($id));
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
			'nama', 'gender', 'nisn', 'nik', 'tempat_lahir', 'tanggal_lahir', 'agama', 'kewarganegaraan', 'penyakit_berat', 'golongan_darah', 'kebutuhan_khusus', 'transportasi', 'fk_tinggi_berat', 'anak_ke', 'jumlah_saudara', 'no_kps', 'no_kip', 'no_kks', 'reg_akta', 'fk_foto'
		]);

		$validator = Validator::make($input, [
			'fk_foto' => 'nullable|exists:foto,foto_id',
			'nisn' => 'required|unique:siswa,nisn,' . $id,
			'nik' => 'required',
			'nama' => 'required',
			'kewarganegaraan' => 'required',
			'kebutuhan_khusus' => 'nullable',
			'agama' => 'required',
			'gender' => 'required|in:L,P',
			'tanggal_lahir' => 'required|date',
			'tempat_lahir' => 'required',
			'jumlah_saudara' => 'required|numeric',
			'anak_ke' => 'required|numeric',
			'penyakit_berat' => 'nullable',
			'transportasi' => 'required',
			'golongan_darah' => 'required|in:A,B,AB,O',
			'no_kps' => 'nullable|numeric',
			'no_kip' => 'nullable|numeric',
			'no_kks' => 'nullable|numeric',
			'reg_akta' => 'nullable|numeric',
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$siswa = Siswa::findOrFail($id);
			unset($input['fk_foto']);
			$siswa->fill($input)->save();

			$foto = $request->fk_foto;
			if (!empty($foto)) {
				$siswa->avatar()->detach();
				$siswa->avatar()->attach($foto, ['nama' => 'avatar', 'deskripsi' => 'foto profil', 'role' => 'profile']);
			}

			return new RSiswa($siswa);
		} catch (\Throwable $th) {
			dd($th);
			// return $this->ResponseError([], self::QUERY_ED);
		}
	}

	/**
	 * Restore the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function restore($id)
	{
		$siswa = Siswa::findOrFail($id);
		$siswa->restore();
		return new RSiswa($siswa);
	}

	/**
	 * Thrash the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	// public function destroy($id)
	// {
	// 	$siswa = Siswa::findOrFail($id);
	// 	$siswa->delete();
	// 	return new RSiswa($siswa);
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
			$siswa = Siswa::whereIn('id', $id)->delete();
		} else {
			$siswa = Siswa::findOrFail($id);
			$siswa->forceDelete();
		}
		return response()->json(['data' => $siswa, 'message' => 'Berhasil menghapus siswa!'], 200);
	}

	/**
	 * Link orangtua with siswa
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function LinkOrangtua(Request $request, $id)
	{
		$validator = Validator::make($request->only(['id', 'tinggal_bersama']), [
			'id' => 'array',
			'id.*' => 'numeric',
			'tinggal_bersama' => 'array',
			'tinggal_bersama.*' => 'boolean'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$siswa = Siswa::findOrFail($id);
		$ids = $request->id;
		$opts = $request->tinggal_bersama;
		$newLinks = [];

		foreach ($ids as $key => $id) {
			$newLinks[$id] = ['tinggal_bersama' => $opts[$key]];
		}
		$siswa->orangtua()->syncWithoutDetaching($newLinks);

		return response()->json(['message' => 'Berhasil menyambungkan orangtua!'], 200);
	}

	public function LinkChangeOrangtua(Request $request, $id)
	{
		$validator = Validator::make($request->only(['fk_orangtua']), [
			'fk_orangtua' => 'nullable|array',
			'fk_orangtua.id' => 'numeric',
			'fk_orangtua.tinggal_bersama' => 'boolean'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$siswa = Siswa::findOrFail($id);

		$siswa->orangtua()->updateExistingPivot(
			$request->fk_orangtua['id'],
			['tinggal_bersama' =>  $request->fk_orangtua['tinggal_bersama']]
		);

		return response()->json(['message' => 'Berhasil menyambungkan orangtua!'], 200);
	}

	/**
	 * Unlink orangtua with siswa
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function UnLinkOrangtua(Request $request, $id)
	{
		$validator = Validator::make($request->only(['fk_orangtua']), [
			'fk_orangtua' => 'nullable|array',
			'fk_orangtua.*' => 'numeric'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$siswa = Siswa::findOrFail($id);

		$links = $request->fk_orangtua;
		$siswa->orangtua()->detach($links);

		return response()->json(['message' => 'Berhasil memutuskan orangtua!'], 200);
	}

	/**
	 * Link kelas with siswa
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function LinkKelas(Request $request, $id)
	{
		$validator = Validator::make($request->only(['fk_kelas']), [
			'fk_kelas' => 'nullable|array',
			'fk_kelas.*' => 'numeric'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$siswa = Siswa::findOrFail($id);
		$siswa->kelas()->syncWithoutDetaching($request->post('fk_kelas'));

		return response()->json(['message' => 'Berhasil menyambungkan kelas!'], 200);
	}

	/**
	 * Unlink kelas with siswa
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function UnLinkKelas(Request $request, $id)
	{
		$validator = Validator::make($request->only(['fk_kelas']), [
			'fk_kelas' => 'nullable|array',
			'fk_kelas.*' => 'numeric'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$siswa = Siswa::findOrFail($id);
		$siswa->kelas()->detach($request->post('fk_kelas'));

		return response()->json(['message' => 'Berhasil memutuskan kelas!'], 200);
	}

	/**
	 * Link tinggi berat with siswa
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function LinkTinggiBerat(Request $request, $id)
	{
		$validator = Validator::make($request->only(['tinggi', 'berat']), [
			'tinggi' => 'required|numeric',
			'berat' => 'required|numeric'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$siswa = Siswa::findOrFail($id);
		$tinggiBerat = TinggiBerat::create($request->only(['tinggi', 'berat']));
		$siswa->tinggiberat()->attach($tinggiBerat->id);

		return response()->json(['message' => 'Berhasil menyambungkan tinggi berat!'], 200);
	}

	/**
	 * Unlink tinggi berat with siswa
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function UnlinkTinggiBerat(Request $request, $id)
	{
		$validator = Validator::make($request->only(['fk_tinggi_berat']), [
			'fk_tinggi_berat' => 'nullable|array',
			'fk_tinggi_berat.*' => 'numeric'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$siswa = Siswa::findOrFail($id);
		$siswa->tinggiberat()->detach($request->post('fk_tinggi_berat'));

		return response()->json(['message' => 'Berhasil memutuskan tinggi berat!'], 200);
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

		$siswa = Siswa::findOrFail($id);

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

	public function ShowUnattended(Request $request)
	{
		$query = Siswa::query()->doesntHave('kelas');
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


	public function ShowOrangtua($id)
	{
		$siswa = Siswa::findOrFail($id);
		return new RCOrangtua($siswa->orangtua);
	}

	public function ShowKelas($id)
	{
		$siswa = Siswa::with('kelas')->findOrFail($id);
		return new RCKelas($siswa->kelas);
	}

	public function ShowTinggiBerat($id)
	{
		$siswa = Siswa::findOrFail($id);
		return new RCTinggiBerat($siswa->tinggiberat);
	}

	public function ResetPassword($id)
	{
		$siswa = Siswa::findOrFail($id);
		$password = Carbon::create($siswa->tanggal_lahir)->format('d/m/Y');
		$user =  $siswa->user()->updateOrCreate(
			["fk_siswa" => $id],
			[
				"username" => $siswa->nis,
				"password" => bcrypt($password),
				"fk_siswa" => $siswa->id
			]
		);
		return response()->json(["message" => $password], 200);
	}

	public function ShowSiswaDashboard($id)
	{
		$siswa = Siswa::with(['kelas.jadwal', 'kelas.pegawai', 'kelas.pembelajaran'])->findOrFail($id);
		return new RSiswa($siswa);
	}

	public function ShowNilaiDashboard($id)
	{
		$siswa = Siswa::with('nilai.indikator.pembelajaran')->findOrFail($id);
		return new RCNilai($siswa->nilai->sortBy('indikator.nama'));
	}

	public function ShowCatatanDashboard($id)
	{
		$siswa = Siswa::with('catatan')->findOrFail($id);
		return new RCCatatan($siswa->catatan->sortBy('catatan.tanggal'));
	}

	public function ReportSiswa()
	{
		//* ambil siswa per tahun
		$siswaTahun = Siswa::selectRaw('year(created_at) year, count(*) data')
			->groupBy('year')
			->orderBy('year', 'desc')
			->get()->toArray();
		$now = Carbon::now()->format('Y');
		$siswaNow = Siswa::whereYear('created_at', $now)->get();

		$siswaAlamat = [];
		foreach ($siswaNow as $key => $siswa) {
			$array = $siswa->orangtua
				->groupBy('pekerjaan')->toArray();
			$siswaAlamat = array_merge($siswaAlamat, ...array_map(function ($data, $key) {
				return [$key => sizeof($data)];
			}, $array, array_keys($array)));
		}
		return [
			'tahun' => $siswaTahun,
			'alamat' => $siswaAlamat
		];
	}

	public function ReportNilai(Request $request, Kelas $kelas, $siswa)
	{
		$pembelajaran = $kelas->pembelajaran;
		$pembelajaranId = $pembelajaran->map(function ($item, $key) {
			return $item->id;
		});
		$indikator = Indikator::whereIn('fk_pembelajaran', $pembelajaranId)->get();
		$indikatorId = $indikator->map(function ($item, $key) {
			return $item->id;
		});
		$indikator = $indikator->groupBy('fk_pembelajaran');

		$nilai = Nilai::whereIn('fk_indikator', $indikatorId)->where(['fk_kelas' => $kelas->id, 'fk_siswa' => $siswa])->get();
		$nilai = $nilai->groupBy('fk_indikator');

		$siswaNilai = $pembelajaran->map(function ($item, $key) use ($indikator, $nilai) {
			$item = $item->toArray();
			if ($indikator->has($item['id'])) {
				$indikator = $indikator->get($item['id'])->map(function ($item, $key) use ($nilai) {
					$item = $item->toArray();
					if ($nilai->has($item['id']))
						$item['nilai'] = $nilai->get($item['id'])->toArray();
					else $item['nilai'] = [];
					return $item;
				});
				$item['indikator'] = $indikator->toArray();
			} else $item['indikator'] = [];

			return $item;
		});
		// $array = Nilai::with(['indikator', 'indikator.pembelajaran' => function ($q) use ($pembelajaran) {
		// 	$q->whereIn('id',$pembelajaran);
		// }])->get()->toArray();
		return $siswaNilai->toArray();
	}
}
