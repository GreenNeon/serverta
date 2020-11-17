<?php

namespace App\Helper;

/**
 * search: {text, [column]}
 */
class SearchClass
{
	private $text;
	private $table;
	private $columns;

	public function __construct($obj)
	{
		// parsing object
		if (gettype($obj) === 'string') $obj = json_decode($obj, true);

		$this->text = $obj['_text'] ?? '';
		$this->columns = $obj['_columns'] ?? [];
		$this->table = [];
		foreach ($this->columns as $key => $column) {
			$nobj = explode(".", $column, 2);
			if (sizeof($nobj) >= 2) {
				$this->table[$key] = $nobj[0];
				$this->columns[$key] = $nobj[1];
			} else $this->table[$key] = null;
		}
	}
	public function GetTable()
	{
		return $this->table;
	}
	public function GetText()
	{
		return $this->text;
	}
	public function GetColumns()
	{
		return $this->columns;
	}
}
