<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\NilaiCollection as RCNilai;
use App\Http\Resources\Nilai as RNilai;
use App\Models\Nilai;
use App\Helper\Traits\Table;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class NilaiController extends ApiBaseController
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
		$query = Nilai::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like']);
		try {
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			if (isset($settings['_limit']) && $settings['_limit'] === 'inf') {
				return new RCNilai($query->get());
			} else {
				if (!empty($settings['_limit']) && !is_int($settings['_limit'])) $settings['_limit'] = 5;
				return new RCNilai($query->paginate($settings['_limit']));
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
			'nilai', 'catatan', 'fk_indikator', 'fk_siswa', 'fk_kelas', 'created_at', 'created_at', 'updated_at'
		]);
		$validator = Validator::make($input, [
			'nilai' => 'required|numeric',
			'catatan' => 'nullable',
			'fk_indikator' => 'required|exists:indikator,id,deleted_at,NULL',
			'created_at' => 'required|date',
			'updated_at' => 'required|date',
			'fk_siswa' => 'required|exists:siswa,id,deleted_at,NULL',
			'fk_kelas' => 'required|exists:kelas,id,deleted_at,NULL'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$nilai = new Nilai;
			$nilai->timestamps = false;
			$nilai->fill($input);
			$nilai->save();
			return new RNilai($nilai);
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
		return new RNilai(Nilai::findOrFail($id));
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
			'nilai', 'catatan', 'fk_indikator', 'fk_siswa', 'fk_kelas'
		]);
		$validator = Validator::make($input, [
			'nilai' => 'numeric',
			'catatan' => 'nullable',
			'fk_indikator' => 'exists:indikator,id',
			'fk_siswa' => 'exists:siswa,id',
			'fk_kelas' => 'exists:kelas,id'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$nilai = Nilai::findOrFail($id);
			$nilai->fill($input)->save();
			return new RNilai($nilai);
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
		$nilai = Nilai::findOrFail($id);
		$nilai->restore();
		return new RNilai($nilai);
	}

	/**
	 * Thrash the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$nilai = Nilai::findOrFail($id);
		$nilai->delete();
		return new RNilai($nilai);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function remove($id)
	{
		$nilai = Nilai::findOrFail($id);
		$nilai->forceDelete();
		return new RNilai($nilai);
	}
}
