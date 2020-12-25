<?php

class shopCatalogreviewsWaViewBuffer implements shopCatalogreviewsViewBuffer
{
	protected $view;

	public function __construct()
	{
		$this->view = new waSmarty3View(wa(), [
			'compile_id' => 'catalogreviews',
		]);

		$smarty_modifiers = new shopCatalogreviewsSmartyModifiers();
		$smarty_modifiers->registerModifiers($this->view->smarty);

		$this->view->smarty->caching = false;
	}

	public function assign($name, $value = null)
	{
		$this->view->assign($name, $value);
	}

	public function getVars($name = null)
	{
		return $this->view->getVars($name);
	}

	public function render($template)
	{
		if (strpos($template, '{') === false)
		{
			return $template;
		}

		try
		{
			$result = $this->view->fetch('string:' . $template);

			if ($result === '')
			{
				return " ";
			}

			return $result;
		}
		catch (SmartyCompilerException  $e)
		{
			return '(!) ' . $template;
		}
	}

	public function renderAll($templates)
	{
		foreach ($templates as $i => $template)
		{
			$templates[$i] = $this->render($template);
		}

		return $templates;
	}

	public function clearAllAssign()
	{
		$this->view->clearAllAssign();
	}
}
