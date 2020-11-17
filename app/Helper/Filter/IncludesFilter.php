<?php

namespace App\Helper\Filter;

class IncludesFilter
{
	private $table;
	private $column;
	private $texts;

	public function __construct($obj)
	{
		// inc: {column, [text]}
		$this->column = $obj['_column'] ?? '';
		$nobj = explode(".", $this->column, 2);
		if (sizeof($nobj) >= 2) {
			$this->table = $nobj[0];
		} else $this->table = null;

		$this->texts = $obj['texts'] ?? '';
	}
	public function GetTable()
	{
		return $this->table;
	}
	public function GetColumn()
	{
		return $this->column;
	}
	public function GetText()
	{
		return $this->texts;
	}
}