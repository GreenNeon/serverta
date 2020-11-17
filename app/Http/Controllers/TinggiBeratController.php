<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\TinggiBeratCollection as RCTinggiBerat;
use App\Http\Resources\TinggiBerat as RTinggiBerat;
use App\Models\TinggiBerat;
use App\Helper\Traits\Table;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class TinggiBeratController extends ApiBaseController
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
		$query = TinggiBerat::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like']);
		try {
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			if (isset($settings['_limit']) && $settings['_limit'] === 'inf') {
				return new RCTinggiBerat($query->get());
			} else {
				if (!empty($settings['_limit']) && !is_int($settings['_limit'])) $settings['_limit'] = 5;
				return new RCTinggiBerat($query->paginate($settings['_limit']));
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
		$query = TinggiBerat::query();
		$settings = $request->only(['_like']);
		try {
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			return new RCTinggiBerat($query->orderBy('nama')->take(5)->get());
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
		$input = $request->only(['berat', 'tinggi']);
		$validator = Validator::make($input, [
			'berat' => 'required|number|gt:0',
			'tinggi' => 'required|number|gt:0'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$tinggiberat = TinggiBerat::create($input);
		} catch (\Throwable $th) {
			return $this->ResponseError([], self::QUERY_SV);
		} finally {
			return new RTinggiBerat($tinggiberat);
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
		return new RTinggiBerat(TinggiBerat::findOrFail($id));
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
		$input = $request->only(['berat', 'tinggi']);
		$validator = Validator::make($input, [
			'berat' => 'number|gt:0',
			'tinggi' => 'number|gt:0'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$tinggiberat = TinggiBerat::findOrFail($id);
			$tinggiberat->fill($input)->save();
		} catch (\Throwable $th) {
			return $this->ResponseError([], self::QUERY_ED);
		} finally {
			return new RTinggiBerat($tinggiberat);
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
		$tinggiberat = Tinggiberat::findOrFail($id);
		$tinggiberat->siswa()->sync([]);
		$tinggiberat->delete();
		return new RTinggiberat($tinggiberat);
	}
}
