<?php

class shopLinkcanonicalPluginRepairActions extends waJsonActions
{
	protected $response = 'Ok';

	public function defaultAction()
	{
		$this->response = "Available repair actions:\r\n\tclean";
	}

	public function cleanAction()
	{
		$cleaner = new shopLinkcanonicalCleaner();
		$cleaner->clean();
	}

	public function run($params = null)
	{
		$action = $params;
		if (!$action)
		{
			$action = 'default';
		}
		$this->action = $action;
		$this->preExecute();
		$this->execute($this->action);
		$this->postExecute();

		if ($this->action == $action)
		{
			if (waRequest::isXMLHttpRequest())
			{
				$this->getResponse()->addHeader('Content-type', 'application/json');
			}
			$this->getResponse()->sendHeaders();
			if (!$this->errors)
			{
				echo '<pre>' . $this->response . '</pre>';
			}
			else
			{
				echo '<pre>' . json_encode(array('status' => 'fail', 'errors' => $this->errors)) . '</pre>';
			}
		}
	}
}