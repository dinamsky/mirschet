<?php

class shopCatalogreviewsWaProductReviewsSource extends shopProductReviewsModel implements shopCatalogreviewsProductReviewsSource
{
	public function getByCollectionOptions(
		shopCatalogreviewsProductReviewsCollectionOptions $reviews_collection_options,
		$offset,
		$limit
	)
	{
		/** @var shopCatalogreviewsWaProductReviewsCollection $collection */
		list($collection, $select_fields) = $this->buildCollection($reviews_collection_options);
		$sql = $this->buildQuery($collection, $select_fields, $offset, $limit);

		$reviews = $this->query($sql)->fetchAll();

		if ($reviews_collection_options->with_replies)
		{
			if ($reviews_collection_options->is_plain_replies_structure)
			{
				$this->loadRepliesToReviewsPlain($reviews_collection_options, $reviews);
			}
			else
			{
				$this->loadRepliesToReviewsAsTree($reviews_collection_options, $reviews);
			}
		}

		$this->loadContactDataToReviews($reviews);

		return $reviews;
	}

	public function countByCollectionOptions(shopCatalogreviewsProductReviewsCollectionOptions $reviews_collection_options)
	{
		list($collection, $select_fields) = $this->buildCollection($reviews_collection_options);

		return $collection->count();
	}

	public function unpublishReview($review_id)
	{
		return $this->updateByField('id', $review_id, [
			'status' => self::STATUS_DELETED,
		]);
	}

	public function publishReview($review_id)
	{
		return $this->updateByField('id', $review_id, [
			'status' => self::STATUS_PUBLISHED,
		]);
	}

	public function deleteReview($review_id)
	{
		$review = $this
			->select('left_key,right_key')
			->where('id = :id', ['id' => $review_id])
			->fetchAssoc();
		if (!$review)
		{
			return false;
		}

		$delete_sql = "
DELETE FROM {$this->table}
WHERE id = :id
	OR (left_key > :left_key AND right_key < :right_key)
";

		$delete_query_params = [
			'id' => $review_id,
			'left_key' => $review['left_key'],
			'right_key' => $review['right_key'],
		];

		return $this->query($delete_sql, $delete_query_params)->result();
	}

	public function addReviewReply($contact_id, $review_id, $reply_text)
	{
		$product_id = $this
			->select('product_id')
			->where('id = :id', ['id' => $review_id])
			->fetchField();

		if (!wa_is_int($product_id) || !($product_id > 0))
		{
			return false;
		}

		$reply_assoc = [
			'product_id' => $product_id,
			'status' => self::STATUS_PUBLISHED,
			'title' => null,
			'text' => $reply_text,
			'rate' => null,
			'contact_id' => $contact_id,
			'auth_provider' => self::AUTH_USER,
		];

		return $this->add($reply_assoc, $review_id);
	}

	private function buildQuery(
		shopCatalogreviewsWaProductReviewsCollection $collection,
		array $select_fields,
		$offset,
		$limit
	)
	{
		$from_and_where_statement = $collection->getSQL();

		// todo адаптировать для set
		// for dynamic set
		//$hash = $collection->_getProtectedValue('hash');
		//$info = $collection->_getProtectedValue('info');
		//if (
		//	$hash[0] == 'set' && !empty($info['id'])
		//	&& $info['type'] == shopSetModel::TYPE_DYNAMIC
		//)
		//{
		//	$count = $collection->count();
		//	if ($offset + $limit > $count)
		//	{
		//		$limit = $count - $offset;
		//	}
		//}

		$protected_value = $collection->_getProtectedValue('group_by');
		$distinct = $collection->_getProtectedValue('joins') && !$protected_value ? 'DISTINCT ' : '';

		$select_fields_statement = implode(',', $select_fields);

		$sql = "SELECT {$distinct} {$select_fields_statement}\n";
		$sql .= $from_and_where_statement;
		$sql .= $collection->getGroupByStatement();
		$having = $collection->_getProtectedValue('having');
		if ($having)
		{
			$sql .= "\nHAVING " . implode(' AND ', $having);
		}
		$sql .= $collection->getOrderByStatement();
		$sql .= "\nLIMIT " . ($offset ? $offset . ',' : '') . (int)$limit;

		return $sql;
	}

	private function loadRepliesToReviewsAsTree(shopCatalogreviewsProductReviewsCollectionOptions $reviews_collection_options, array &$reviews)
	{
		if (count($reviews) === 0)
		{
			return;
		}

		$review_ids = [];
		$review_refs_by_id = [];
		foreach ($reviews as $index => $review)
		{
			$review_id = intval($review['id']);
			$review_refs_by_id[$review_id] = &$reviews[$index];
			$review_ids[] = $review_id;
		}

		$replies_query = $this->buildRepliesQuery($reviews_collection_options, $review_ids);

		foreach ($replies_query as $reply)
		{
			if ($reviews_collection_options->published_only && $reply['status'] !== 'approved')
			{
				reset($reply);

				continue;
			}

			$reply_id = $reply['id'];
			$parent_id = $reply['parent_id'];
			if (!array_key_exists($parent_id, $review_refs_by_id))
			{
				// результат битого left_key или подитель не опубликован
				continue;
			}

			if (!array_key_exists('replies', $review_refs_by_id[$parent_id]))
			{
				$review_refs_by_id[$parent_id]['replies'] = [];
			}

			$review_refs_by_id[$parent_id]['replies'][] = &$reply;

			$review_refs_by_id[$reply_id] = &$reply;

			unset($reply);
		}
	}

	private function loadRepliesToReviewsPlain(shopCatalogreviewsProductReviewsCollectionOptions $reviews_collection_options, array &$reviews)
	{
		if (count($reviews) === 0)
		{
			return;
		}

		$review_ids = [];
		$review_refs_by_id = [];
		$review_is_published = [];

		foreach ($reviews as $index => $review)
		{
			$review_id = intval($review['id']);
			$review_refs_by_id[$review_id] = &$reviews[$index];
			$review_ids[] = $review_id;
			$review_is_published[$review_id] = true;
		}

		$replies_query = $this->buildRepliesQuery($reviews_collection_options, $review_ids);

		foreach ($replies_query as $reply)
		{
			if (
				$reviews_collection_options->published_only
				&& ($reply['status'] !== 'approved' || !array_key_exists($reply['parent_id'], $review_is_published))
			)
			{
				reset($reply);

				continue;
			}

			$reply_id = $reply['id'];
			$root_review_id = $reply['review_id'];
			if (!array_key_exists($root_review_id, $review_refs_by_id))
			{
				// результат битого left_key
				continue;
			}

			$review_is_published[$reply['id']] = true;

			if (!array_key_exists('replies', $review_refs_by_id[$root_review_id]))
			{
				$review_refs_by_id[$root_review_id]['replies'] = [];
			}

			$review_refs_by_id[$root_review_id]['replies'][] = &$reply;

			$review_refs_by_id[$reply_id] = &$reply;

			unset($reply);
		}
	}

	private function loadContactDataToReviews(array &$reviews)
	{
		foreach (array_keys($reviews) as $index)
		{
			$review = &$reviews[$index];

			if (isset($review['replies']) && is_array($review['replies']))
			{
				$this->loadContactDataToReviews($review['replies']);
			}

			if (
				wa_is_int($review['contact_id']) && $review['contact_id'] > 0
				&& (empty($review['name']) || empty($review['email']))
			)
			{
				try
				{
					$contact = new waContact($review['contact_id']);
				}
				catch (waException $e)
				{
					$contact = null;
				}

				if ($contact && $contact->exists())
				{
					$review['name'] = $contact->getName();

					$email_data = $contact->getFirst('email');
					if (is_array($email_data))
					{
						$review['email'] = $email_data['value'];
					}
				}
			}
		}
	}

	private function buildCollection(shopCatalogreviewsProductReviewsCollectionOptions $reviews_collection_options)
	{
		$select_fields = ['pr.*', '1 AS count_null'];

		$collection = new shopCatalogreviewsWaProductReviewsCollection($reviews_collection_options->hash);
		$collection->addWhere('pr.parent_id = 0');

		if ($reviews_collection_options->published_only)
		{
			$collection->addWhere('pr.status = \'approved\'');
		}

		if ($reviews_collection_options->sort)
		{
			$order = is_string($reviews_collection_options->order) && strtolower($reviews_collection_options->order) === 'desc'
				? 'desc'
				: 'asc';

			$collection->orderBy($reviews_collection_options->sort, $order);
		}

		if ($reviews_collection_options->add_reviewsplus_plugin)
		{
			$alias = $collection->addLeftJoin('shop_reviewsplus_dop', ':table.review_id = pr.id');

			// todo передать поля явно
			array_unshift($select_fields, "`{$alias}`.*");
		}

		return [$collection, $select_fields];
	}

	/**
	 * @param shopCatalogreviewsProductReviewsCollectionOptions $reviews_collection_options
	 * @param array $review_ids
	 * @return waDbResultSelect
	 */
	private function buildRepliesQuery(shopCatalogreviewsProductReviewsCollectionOptions $reviews_collection_options, array $review_ids)
	{
		$replies_query = $this->select('*')
			->where('review_id IN (:ids)', ['ids' => $review_ids])
			->order('left_key ASC');// todo добавить другие сортировки;

		//if ($reviews_collection_options->published_only)
		//{
		//	$replies_query->where('status = \'approved\'');
		//}

		return $replies_query->query();
	}
}
