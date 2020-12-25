<?php

class shopCatalogreviewsWaProductReviewsCollection extends shopProductsCollection
{
	public function __construct($hash = '', $options = [])
	{
		if (!is_array($options))
		{
			$options = [];
		}

		//$options['filters'] = [
		//	'in_stock_only' => false,
		//	'out_of_stock_only' => false,
		//];

		parent::__construct($hash, $options);
	}

	public function count()
	{
		if ($this->count !== null)
		{
			return $this->count;
		}
		$this->where[] = 'pr.parent_id = 0';
		$this->where[] = 'pr.`status` = \'' . shopProductReviewsModel::STATUS_PUBLISHED . '\'';
		$sql = $this->getSQL();
		array_pop($this->where);

		if ($this->having)
		{
			if ($this->group_by === 'p.id')
			{
				$sql .= 'pr.id';
			}
			else
			{
				$sql .= $this->_getGroupBy();
			}

			$sql .= " HAVING " . implode(' AND ', $this->having);
			$sql = "SELECT COUNT(*) FROM (SELECT pr.* " . $sql . ") AS t";
		}
		else
		{
			$sql = "SELECT COUNT(" . ($this->joins ? 'DISTINCT ' : '') . "pr.id) " . $sql;
		}

		$count = intval($this->getModel()->query($sql)->fetchField());

		return $this->count = $count;
	}

	public function getSQL()
	{
		$this->prepare();

		if (!$this->prepared_event)
		{
			wa('shop')->event('products_collection.prepared', $this);
			$this->prepared_event = true;
		}

		$sql = '
FROM shop_product_reviews AS pr
	JOIN shop_product p
		ON p.id = pr.product_id';

		if ($this->joins)
		{
			foreach ($this->joins as $join)
			{
				$alias = isset($join['alias']) ? $join['alias'] : '';
				if (isset($join['on']))
				{
					$on = $join['on'];
				}
				else
				{
					$on = "p.id = " . ($alias ? $alias : $join['table']) . ".product_id";
				}
				$sql .= "\n\t" . (isset($join['type']) ? $join['type'] . ' ' : '') . "JOIN " . $join['table'] . " "
					. $alias . "\n\t\tON " . $on;
			}
		}

		if (count($this->where) !== 0)
		{
			$sql .= "\nWHERE " . implode("\n\tAND ", $this->where);
		}

		return $sql;
	}

	public function getGroupByStatement()
	{
		$old_group_by = $this->group_by;
		if ($this->group_by === 'p.id')
		{
			$this->group_by = 'pr.id';
		}

		$group_by_statement = $this->_getGroupBy();

		$this->group_by = $old_group_by;

		return $group_by_statement;
	}

	public function getOrderByStatement()
	{
		return $this->_getOrderBy();
	}

	public function _getProtectedValue($field)
	{
		return $this->$field;
	}

	public function orderBy($field, $order = 'ASC')
	{
		if (
			$field === 'name'
			|| $field === 'price'
			|| $field === 'total_sales'
			|| $field === 'stock'
		)
		{
			return parent::orderBy($field, $order);
		}
		elseif ($field === 'rating')
		{
			return parent::orderBy('COALESCE(pr.rate, 0)', $order);
		}
		elseif ($field === 'create_datetime')
		{
			return parent::orderBy('pr.datetime', $order);
		}

		return '';
	}

	public function addLeftJoin($table, $on = null, $where = null)
	{
		$alias = $this->addJoin($table, $on, $where);

		$this->joins[count($this->joins) - 1]['type'] = 'LEFT';

		return $alias;
	}

	public function popJoin()
	{
		return array_pop($this->joins);
	}

	protected function frontendConditions()
	{
		$drop_out_of_stock_current = waRequest::param('drop_out_of_stock');
		waRequest::setParam('drop_out_of_stock', false);

		parent::frontendConditions();

		waRequest::setParam('drop_out_of_stock', $drop_out_of_stock_current);
	}

	public function filters($data)
	{
		$drop_out_of_stock_current = waRequest::param('drop_out_of_stock');
		waRequest::setParam('drop_out_of_stock', false);

		parent::filters($data);

		waRequest::setParam('drop_out_of_stock', $drop_out_of_stock_current);
	}

	protected function getFields($fields)
	{
		$drop_out_of_stock_current = waRequest::param('drop_out_of_stock');
		waRequest::setParam('drop_out_of_stock', false);

		$result = parent::getFields($fields);

		waRequest::setParam('drop_out_of_stock', $drop_out_of_stock_current);

		return $result;
	}

	protected function _getOrderBy()
	{
		$drop_out_of_stock_current = waRequest::param('drop_out_of_stock');
		waRequest::setParam('drop_out_of_stock', false);

		$result = parent::_getOrderBy();

		waRequest::setParam('drop_out_of_stock', $drop_out_of_stock_current);

		return $result;
	}
}
