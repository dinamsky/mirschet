<?php

class shopSeoredirectErrorsModel extends waModel
{
	protected $table = 'shop_seoredirect_errors';

	public function addError($domain, $url, $error_code)
	{
		if (preg_match('~.*/ordercall-config/$~', $url))
		{
			return;
		}

		$hash = $this->getHashByDomainAndUrl($domain, $url);
		$error = $this->getByField(array(
			'hash' => $hash,
			'domain' => $domain,
			'url' => $url,
		));

		if (!empty($error))
		{
			$this->updateById($error['id'], array(
				'http_referer' => waRequest::server('HTTP_REFERER'),
				'views' => $error['views'] + 1,
				'edit_datetime' => date('Y-m-d H:i:s'),
			));
		}
		else
		{
			$data = array(
				'hash' => $hash,
				'domain' => $domain,
				'url' => $url,
				'http_referer' => waRequest::server('HTTP_REFERER'),
				'code' => $error_code,
				'views' => 1,
				'create_datetime' => date('Y-m-d H:i:s'),
				'edit_datetime' => date('Y-m-d H:i:s'),
			);
			$this->insert($data);
		}
	}

	private function getHashByDomainAndUrl($domain, $url)
	{
		return md5($domain . $url);
	}

	/**
	 * @param $id int|array
	 */
	public function delete($id)
	{
		if (!$id)
		{
			return;
		}
		if (!is_array($id))
		{
			$this->deleteById($id);
		}
		else
		{
			$this->deleteByField('id', $id);
		}
	}
}
