<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\BlogCollection as RCBlog;
use App\Http\Resources\Blog as RBlog;
use App\Models\Blog;
use App\Helper\Traits\Table;
use App\Models\Siswa;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class BlogController extends ApiBaseController
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
		$query = Blog::query();
		$settings = $request->only(['page', '_limit', '_filter', '_like']);
		try {
			$query = $this->TableSearch($query, $settings['_like'] ?? []);
			$query = $this->TableFilter($query, $settings['_filter'] ?? []);

			if (isset($settings['_limit']) && $settings['_limit'] === 'inf') {
				return new RCBlog($query->get());
			} else {
				if (!empty($settings['_limit']) && !is_int($settings['_limit'])) $settings['_limit'] = 5;
				return new RCBlog($query->paginate($settings['_limit']));
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
			'title', 'subtitle', 'body'
		]);
		$validator = Validator::make($request->all(), [
			'title' => 'required',
			'subtitle' => 'required',
			'body' => 'required',
			'fk_foto' => 'nullable|exists:foto,foto_id'
		]);

		if ($validator->fails()) {
			return $this->ResponseError($validator->errors(), self::QUERY_VA);
		}

		try {
			$blog = Blog::create($input);

			$foto = $request->fk_foto;
			if (!empty($foto)) $blog->image()->attach($foto, ['nama' => 'Foto Blog', 'deskripsi' => 'foto untuk blog.', 'role' => 'post']);
			return new RBlog($blog);
		} catch (\Throwable $th) {
			return $this->ResponseError([], self::QUERY_SV, $th->getMessage());
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
		$blog = Blog::findOrFail($id);
		return response()->json(['data' => $blog], 200);
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
			'title', 'subtitle', 'body'
		]);

		try {
			$blog = Blog::findOrFail($id);
			$blog->fill($input)->save();
			return new RBlog($blog);
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
		$blog = Blog::findOrFail($id);
		$blog->restore();
		return new RBlog($blog);
	}

	/**
	 * Thrash the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	// public function destroy($id)
	// {
	// 	$blog = Blog::findOrFail($id);
	// 	$blog->delete();
	// 	return new RBlog($blog);
	// }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$blog = Blog::findOrFail($id);
		$blog->forceDelete();
		return new RBlog($blog);
	}
}
