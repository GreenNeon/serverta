<?php

namespace App\Helper\Filter;

class OrderFilter
{
	private $column;
	private $type;

	public function __construct($obj)
	{
		// order: {column, order}
		$this->column = $obj['_column'] ?? '';
		$this->type = $obj['_type'];
		if ($this->type !== 'asc' && $this->type !== 'desc') $this->type = 'asc';
	}
	public function GetColumn()
	{
		return $this->column;
	}
	public function GetType()
	{
		return $this->type;
	}
}
