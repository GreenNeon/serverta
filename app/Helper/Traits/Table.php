<?php

namespace App\Helper\Traits;

use App\Helper\FilterClass;
use App\Helper\SearchClass;

trait Table
{
	/**
	 * Function to add relation to query
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param array $obj
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	function TableWith($query, $obj, $whitelist)
	{
		// parsing object
		if (!empty($obj)) {
			$safeWith = [];
			foreach ($obj as $key => $relation) {
				if (!in_array($relation, $whitelist, true)) continue;
				$safeWith[] = $relation;
			}

			$query = $query->with($safeWith);
		}

		return $query;
	}

	/**
	 * Function to add search logic in table
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param array $obj
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	function TableSearch($query, $obj)
	{
		// parsing object
		$search = new SearchClass($obj);

		if (!empty($search->GetColumns())) {
			$query->where(function ($query) use ($search) {
				$table = $search->GetTable();
				foreach ($search->GetColumns() as $key => $column) {
					$tableN = $table[$key];
					if (empty($tableN)) {
						if (!empty($search->GetText()) && !empty($column)) $query->orWhere($column, 'LIKE', '%' . $search->GetText() . '%');
					} else {
						if (!empty($search->GetText()) && !empty($column)) {
							$query->whereHas($tableN, function ($query) use ($search, $column) {
								$query->orWhere($column, 'LIKE', '%' . $search->GetText() . '%');
							});
						}
					}
				}
			});
		}
		return $query;
	}

	/**
	 * Function to add search logic in table
	 * * order: [{column, order}]
	 * * inc: {column, [text]}
	 * * gt/lt/e: {column, property}
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param array $obj
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	function TableFilter($query, $obj)
	{
		// parsing object
		$filter = new FilterClass($obj);
		$order = $filter->GetOrder();
		$includes = $filter->GetIncludes();
		$symbol = $filter->GetSymbol();

		if (!empty($symbol)) {
			if (is_array($symbol)) {
				//! symbol array
				for ($i = 0; $i < sizeof($symbol); $i++) {
					//! check table
					$sys = $symbol[$i];
					$table = $sys->GetTable();
					if (empty($table)) {
						if (!empty($sys->GetColumn()))
							$query->where($sys->GetColumn(), $sys->GetType(), $sys->GetText());
					} else {
						//! pake kalo ada
						if (!empty($sys->GetColumn())) {
							$query->whereHas($table, function ($query) use ($sys) {
								$query->where($sys->GetColumn(), $sys->GetType(), $sys->GetText());
							});
						}
					}
				}
			} else {
				//! bukan array
				$table = $symbol->GetTable();
				//! check table
				if (empty($table)) {
					if (!empty($symbol->GetColumn()))
						$query->where($symbol->GetColumn(), $symbol->GetType(), $symbol->GetText());
				} else {
					//!pake kalo ada
					if (!empty($symbol->GetColumn())) {
						$query->whereHas($table, function ($query) use ($symbol) {
							$query->where($symbol->GetColumn(), $symbol->GetType(), $symbol->GetText());
						});
					}
				}
			}
		}
		//dd($query->toBase()->toSql());


		if (!empty($includes)) {
			if (is_array($includes)) {
				//! includes array
				for ($i = 0; $i < sizeof($includes); $i++) {
					//! check table
					$include = $includes[$i];
					$table = $include->GetTable();
					if (empty($table)) {
						if (is_array($include->GetText())) {
							$query->whereIn($include->GetColumn(), $include->GetText());
						} else $query->whereIn($include->GetColumn(), array($include->GetText()));
					} else {
						//! pake kalo ada
						$query->whereHas($table, function ($query) use ($include) {
							if (is_array($include->GetText())) {
								$query->orWhere($include->GetColumn(), 'in', '[' . implode(",", $include->GetText()) . ']');
							} else {
								$query->orWhere($include->GetColumn(), 'in', '[' . $include->GetText() . ']');
							}
						});
					}
				}
			} else {
				//! bukan array
				//! check table
				$table = $includes->GetTable();
				if (empty($table)) {
					$query->whereIn($includes->GetColumn(), array($includes->GetText()));
				} else {
					//! pake kalo ada
					$query->whereHas($table, function ($query) use ($includes) {
						if (is_array($includes->GetText())) {
							$query->whereIn($includes->GetColumn(), $includes->GetText());
						} else {
							$query->whereIn($includes->GetColumn(), array($includes->GetText()));
						}
					});
				}
			}
		}

		if (!empty($order)) {
			if (is_array($order)) {
				for ($i = 0; $i < sizeof($order); $i++) {
					$query->orderBy($order[$i]->GetColumn(), $order[$i]->GetType());
				}
			} else $query->orderBy($order->GetColumn(), $order->GetType());
		}
		return $query;
	}
}
