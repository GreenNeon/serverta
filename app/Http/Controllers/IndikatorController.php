<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\IndikatorCollection as RCIndikator;
use App\Http\Resources\Indikator as RIndikator;
use App\Models\Indikator;
use App\Helper\Traits\Table;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class IndikatorController extends ApiBaseController
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
		$query = Indikator::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like', '_with']);
		$with = ['pembelajaran'];
		try {
			$query = $this->TableWith($query, $settings['_with'] ?? [], $with);
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			if (isset($settings['_limit']) && $settings['_limit'] === 'inf') {
				return new RCIndikator($query->get());
			} else {
				if (!empty($settings['_limit']) && !is_int($settings['_limit'])) $settings['_limit'] = 5;
				return new RCIndikator($query->paginate($settings['_limit']));
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
		$query = Indikator::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like', '_with']);

		$with = ["pembelajaran"];
		try {
			$query = $this->TableWith($query, $settings['_with'] ?? [], $with);
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			return new RCIndikator($query->orderBy('id')->take(5)->get());
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
		$input = $request->only(['nama', 'deskripsi', 'fk_pembelajaran']);
		$validator = Validator::make($input, [
			'nama' => 'required',
			'deskripsi' => 'nullable',
			'fk_pembelajaran' => 'required|exists:pembelajaran,id',
			'fk_foto' => 'nullable|exists:foto,foto_id'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$indikator = Indikator::create($input);

			$foto = $request->fk_foto;
			if (!empty($foto)) $indikator->avatar()->attach($foto, ['nama' => 'avatar', 'deskripsi' => 'foto profil', 'role' => 'profile']);
			return new RIndikator($indikator);
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
		return new RIndikator(Indikator::with('pembelajaran')->findOrFail($id));
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
		$input = $request->only(['nama', 'deskripsi', 'fk_pembelajaran']);
		$validator = Validator::make($input, [
			'deskripsi' => 'nullable',
			'fk_pembelajaran' => 'exists:pembelajaran,id'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$indikator = Indikator::findOrFail($id);
			$indikator->fill($input)->save();
			return new RIndikator($indikator);
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
	// 	$indikator = Indikator::findOrFail($id);
	// 	$indikator->restore();
	// 	return new RIndikator($indikator);
	// }

	/**
	 * Thrash the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	// public function destroy($id)
	// {
	// 	$indikator = Indikator::findOrFail($id);
	// 	$indikator->delete();
	// 	return new RIndikator($indikator);
	// }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$indikator = Indikator::findOrFail($id);
		$indikator->forceDelete();
		return new RIndikator($indikator);
	}
}
