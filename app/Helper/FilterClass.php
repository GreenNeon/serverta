<?php

namespace App\Helper;

use App\Helper\Filter\OrderFilter;
use App\Helper\Filter\IncludesFilter;
use App\Helper\Filter\SymbolFilter;


/**
 * order: {column, order}
 * inc: {column, [text]}
 * gt/lt/e: {column, property}
 */
class FilterClass
{
	/** @var OrderFilter[]|OrderFilter */
	private $orders;
	/** @var IncludesFilter[]|IncludesFilter */
	private $includes;
	/** @var SymbolFilter[]|SymbolFilter */
	private $symbols;

	public function __construct($obj)
	{
		// parsing object
		if(gettype($obj) === 'string') $obj = json_decode($obj, true);

		if (!empty($obj['_order']) && !$this->IsArrayAssoc($obj['_order'])) {
			foreach ($obj['_order'] as $key => $data) {
				$this->orders[] = new OrderFilter($data);
			}
		} else if (!empty($obj['_order'])) $this->orders = new OrderFilter($obj['_order']);

		if (!empty($obj['_includes']) && !$this->IsArrayAssoc($obj['_includes'])) {
			foreach ($obj['_includes'] as $key => $data) {
				$this->includes[] = new IncludesFilter($data);
			}
		} else if (!empty($obj['_includes'])) $this->includes = new IncludesFilter($obj['_includes']);

		if (!empty($obj['_symbol']) && !$this->IsArrayAssoc($obj['_symbol'])) {
			foreach ($obj['_symbol'] as $key => $data) {
				$this->symbols[] = new SymbolFilter($data);
			}
		} else if (!empty($obj['_symbol'])) $this->symbols = new SymbolFilter($obj['_symbol']);
	}
	private function IsArrayAssoc($array)
	{
		return ($array !== array_values($array));
	}
	public function GetOrder()
	{
		return $this->orders;
	}
	public function GetIncludes()
	{
		return $this->includes;
	}
	public function GetSymbol()
	{
		return $this->symbols;
	}
}
