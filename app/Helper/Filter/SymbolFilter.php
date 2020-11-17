<?php

namespace App\Helper\Filter;

class SymbolFilter
{
	private $type;
	private $column;
	private $table;
	private $text;

	public function __construct($obj)
	{
		// order: {column, order}
		$this->column = $obj['_column'] ?? '';
		$nobj = explode(".", $this->column, 2);
		if (sizeof($nobj) >= 2) {
			$this->table = $nobj[0];
		} else $this->table = null;

		$this->text = $obj['_text'] ?? '';
		if (is_numeric($this->text)) $this->text = (int) $this->text;
		$this->type = $obj['_type'];
		if (!in_array($this->type, array('gt', 'gte', 'lt', 'lte', 'e', 'nt'), true)) $this->type = 'e';
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
		return $this->text;
	}
	public function GetType()
	{
		switch ($this->type) {
			case 'gt':
				return '>';
			case 'gte':
				return '>=';
			case 'lt':
				return '<';
			case 'lte':
				return '<=';
			case 'nt':
				return '!=';
			case 'e':
				return '=';
		}
		return $this->type;
	}
}
