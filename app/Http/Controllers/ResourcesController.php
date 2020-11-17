<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\FotoCollection as RCFoto;
use App\Http\Resources\Foto as RFoto;
use App\Models\Foto;
use App\Helper\Traits\Table;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class ResourcesController extends ApiBaseController
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
		$query = Foto::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like']);
		try {
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			if (isset($settings['_limit']) && $settings['_limit'] === 'inf') {
				return new RCFoto($query->get());
			} else {
				if (!empty($settings['_limit']) && !is_int($settings['_limit'])) $settings['_limit'] = 5;
				return new RCFoto($query->paginate($settings['_limit']));
			}
		} catch (QueryException $qe) {
			return $this->ResponseError([], self::QUERY_ID, $qe->getMessage());
		} catch (\Throwable $th) {
			return $this->ResponseError([], self::ANY_UNK, $th->getMessage());
		}
	}

	/**
	 * Link foto with siswa
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function UploadProfil(Request $request)
	{
		$validator = Validator::make($request->only(['foto']), [
			'foto' => 'image|max:512'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		$path = $request->file('foto')->store(
			'avatars',
			'photos'
		);

		try {
			$foto = Foto::create(['file_path' => $path]);

			$base_url = url('/');
			return response()->json(['data' => ['id' => $foto->foto_id, 'profile_url' => "{$base_url}/api/photos/{$foto->foto_id}"], 'message' => 'Berhasil menambahkan foto!'], 200);
		} catch (\Throwable $th) {
			throw $th;
			return $this->ResponseError($validator->errors(), self::ANY_PAR);
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function ShowPhoto(Request $request, $id)
	{
		$foto = Foto::findOrFail($id);
		return response()->file(storage_path('app/photos/'.$foto->file_path));
	}
}
