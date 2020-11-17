<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use JWTAuth;

class ApiBaseController extends Controller
{
	const QUERY_UNK = 'QU499';
	const QUERY_SV = 'QU409';
	const QUERY_ED = 'QU490';
	const QUERY_DE = 'QU408';
	const QUERY_SH = 'QU480';
	const QUERY_ID = 'QU478';
	const QUERY_VA = 'QU487';
	const ANY_UNK = 'AU899';
	const ANY_PAR = 'AU809';
	protected $user = null;

	protected function authenticate()
	{
		$this->user = JWTAuth::parseToken()->authenticate();
	}

	private function DefaultMessage($type) {
		switch ($type) {
			case self::QUERY_UNK:
				return 'Theres something wrong with the query !!';
			case self::QUERY_SV:
				return 'Theres an errors in saving data !!';
			case self::QUERY_ED:
				return 'Theres an errors in updating data !!';
			case self::QUERY_DE:
				return 'Theres an errors in deleting data !!';
			case self::QUERY_SH:
				return 'Theres an errors in fetching data !!';
			case self::QUERY_ID:
				return 'Theres an errors in querying data !!';
			case self::QUERY_VA:
				return 'Theres an errors in validating data !!';
			case self::ANY_UNK:
				return 'Oops something bad happened in the server !!';
			case self::ANY_PAR:
				return 'Oops something bad happened in the server, check your parameter !!';

			default:
				return 'Unknown Errors !!';
		}
	}

	protected function ResponseError($raw = [], $type = 'AU899', $message = '')
	{	
		if($message === '') $message = $this->DefaultMessage($type);
		$data = ["data" => $raw, "message" => $message, "code" => $type];
		return response()->json($data, 400);
	}
}
