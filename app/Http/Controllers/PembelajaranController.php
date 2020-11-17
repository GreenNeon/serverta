<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\PembelajaranCollection as RCPembelajaran;
use App\Http\Resources\IndikatorCollection as RCIndikator;
use App\Http\Resources\Pembelajaran as RPembelajaran;
use App\Models\Pembelajaran;
use App\Helper\Traits\Table;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class PembelajaranController extends ApiBaseController
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
		$query = Pembelajaran::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like', '_with']);
	
		$with = ['indikator'];
		try {
			$query = $this->TableWith($query, $settings['_with'] ?? [], $with);
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			if (isset($settings['_limit']) && $settings['_limit'] === 'inf') {
				return new RCPembelajaran($query->get());
			} else {
				if (!empty($settings['_limit']) && !is_int($settings['_limit'])) $settings['_limit'] = 5;
				return new RCPembelajaran($query->paginate($settings['_limit']));
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
		$query = Pembelajaran::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like']);

		try {
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			return new RCPembelajaran($query->orderBy('nama')->take(5)->get());
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
		$input = $request->only(['nama', 'deskripsi']);
		$validator = Validator::make($input, [
			'nama' => 'required',
			'deskripsi' => 'nullable',
			'fk_foto' => 'nullable|exists:foto,foto_id'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$pembelajaran = Pembelajaran::create($input);

			$foto = $request->fk_foto;
			if (!empty($foto)) $pembelajaran->avatar()->attach($foto, ['nama' => 'avatar', 'deskripsi' => 'foto profil', 'role' => 'profile']);

			return new RPembelajaran($pembelajaran);
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
		return new RPembelajaran(Pembelajaran::findOrFail($id));
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
		$input = $request->only(['nama', 'deskripsi']);
		$validator = Validator::make($input, [
			'nama' => 'required',
			'deskripsi' => 'nullable'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$pembelajaran = Pembelajaran::findOrFail($id);
			$pembelajaran->fill($input)->save();
			return new RPembelajaran($pembelajaran);
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
	public function restore($id)
	{
		$pembelajaran = Pembelajaran::findOrFail($id);
		$pembelajaran->restore();
		return new RPembelajaran($pembelajaran);
	}

	/**
	 * Thrash the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	// public function destroy($id)
	// {
	// 	$pembelajaran = Pembelajaran::findOrFail($id);
	// 	$pembelajaran->delete();
	// 	return new RPembelajaran($pembelajaran);
	// }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$pembelajaran = Pembelajaran::findOrFail($id);
		$pembelajaran->forceDelete();
		return new RPembelajaran($pembelajaran);
	}

	public function ShowIndikator(Request $request, $id)
	{
		$pembelajaran = Pembelajaran::findOrFail($id);
		$query = $pembelajaran->indikator();
		$settings = $request->only(['page', '_limit', '_filter', '_like', '_with']);

		$with = ['indikator'];
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
			return new RCIndikator($data);
		} catch (QueryException $qe) {
			return $this->ResponseError([], self::QUERY_ID, $qe->getMessage());
		} catch (\Throwable $th) {
			return $this->ResponseError([], self::ANY_UNK, $th->getMessage());
		}
	}
}
