<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\AlamatCollection as RCAlamat;
use App\Http\Resources\Alamat as RAlamat;
use App\Models\Alamat;
use App\Helper\Traits\Table;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class AlamatController extends ApiBaseController
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
		$query = Alamat::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like']);
		try {
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			if (isset($settings['_limit']) && $settings['_limit'] === 'inf') {
				return new RCAlamat($query->get());
			} else {
				if (!empty($settings['_limit']) && !is_int($settings['_limit'])) $settings['_limit'] = 5;
				return new RCAlamat($query->paginate($settings['_limit']));
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
			'alamat','rt','rw','dusun','provinsi','kabupaten','kecamatan','kode_wilayah','kode_pos','desa'
			]);
		$validator = Validator::make($input, [
			'dusun' => 'required',
			'provinsi' => 'required',
			'kabupaten' => 'required',
			'kecamatan' => 'required',
			'desa' => 'required',
			'kode_wilayah' => 'nullable',
			'kode_pos' => 'nullable',
			'rt' => 'nullable',
			'rw' => 'nullable',
			'alamat' => 'nullable'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$alamat = Alamat::create($input);
		} catch (\Throwable $th) {
			return $this->ResponseError([], self::QUERY_SV);
		} finally {
			return new RAlamat($alamat);
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
		return new RAlamat(Alamat::findOrFail($id));
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
		'alamat','rt','rw','dusun','provinsi','kabupaten','kecamatan','kode_wilayah','kode_pos','desa'
		]);
		$validator = Validator::make($input, [
			'kode_wilayah' => 'nullable',
			'kode_pos' => 'nullable',
			'rt' => 'nullable',
			'rw' => 'nullable',
			'alamat' => 'nullable'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		
		try {
			$alamat = Alamat::findOrFail($id);
			$alamat->fill($input)->save();
		} catch (\Throwable $th) {
			return $this->ResponseError([], self::QUERY_ED);
		} finally {
			return new RAlamat($alamat);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$alamat = Alamat::findOrFail($id);
		$alamat->delete();
		return new RAlamat($alamat);
	}
}
