<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

class shopSdekintPluginShippingActions extends waJsonActions
{
    /** @var shopSdekintPlugin */
    protected $plugin;

    /** @var shopSdekintPluginCalcRulesModel */
    protected $CalcRules;

    public function defaultAction()
    {
        $per_page = max(2, (int)$this->getConfig()->getOption('products_per_page'));
        if (!$per_page) {
            $per_page = 10;
        }
        $pager = $this->plugin->helper->paginator(
            (int)waRequest::request('page', 1, waRequest::TYPE_INT),
            (int)$this->CalcRules->countAll(),
            $per_page
        );

        $data = array_values(
            (array)$this->CalcRules
                ->select('*')
                ->order('sort, id')
                ->limit((string)ifset($pager, 'limit', 0, 0) . ',' . (string)ifempty($pager, 'limit', 1, $per_page))
                ->fetchAll()
        );
        array_walk($data, function (&$item) {
            foreach (['methods', 'courier', 'point', 'conditions'] as $key) {
                if (array_key_exists($key, $item) && (is_string($item[$key]))) {
                    $item[$key] = @unserialize($item[$key]);
                }
            }
        });
        $pagination = $this->plugin->helper->pagination($pager + ['mode' => 'hash', 'url_before' => '#calccontrol/']);

        $view = wa('shop')->getView();
        $view->assign(compact('pagination', 'data'));

        $this->response['html'] = $view->fetch($this->getTemplate('Default'));
    }

    /**
     * @throws waException
     */
    public function saveAction()
    {
        if ($this->getRequest()->method() !== 'post') {
            throw new waException('Invalid method', 403);
        }

        $data = $this->getRequest()->post('data', '{}', waRequest::TYPE_STRING);
        $data = shopHelper::jsonDecode($data, true);

        try {
            if (!is_array($data)) {
                throw new waException('Неверные данные для записи');
            }
            $this->response['id'] = $this->CalcRules->save($data);
        } catch (Exception $e) {
            $this->errors[] = ['message' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    public function ruleAction()
    {
        $view = wa('shop')->getView();
        $info = array(
            'countries' => []
        );
        $rule = array();

        $id = (int)$this->getRequest()->get('id', 0, waRequest::TYPE_INT);
        if ($id) {
            $rule = $this->CalcRules->findById($id);
        }

        ini_set('xdebug.var_display_max_depth', 10);
        $methods = $this->compatibleShippingMethods();

        $view->assign(compact('methods', 'rule'));

        $this->response['html'] = $view->fetch($this->getTemplate('Rule'));
    }

    public function listAction()
    {
        try {
            $methods = array_filter(
                (array)(new shopPluginModel)->listPlugins(shopPluginModel::TYPE_SHIPPING, ['all' => true]),
                function ($m) {
                    return $m['plugin'] === 'sydsek';
                }
            );
        } catch (waException $e) {
            $this->errors[] = $e->getMessage();
            return;
        }

        $this->response = array_values($methods);
    }

    public function deleteAction()
    {
        $id = (int)$this->getRequest()->post('id', 0, waRequest::TYPE_INT);
        if (!$id) {
            $this->errors[] = ['Invalid ID', 500];
            return;
        }
        try {
            $this->CalcRules->deleteById($id);
            $this->response = 'deleted';
        } catch (waException $e) {
            $this->errors[] = [$e->getMessage(), $e->getCode()];
        }

        return;
    }

    protected function getTemplate($template = '')
    {
        $path = 'plugins/sdekint/templates/actions/backend/CalcRules';
        if ($template) {
            $path .= '/' . $template . '.html';
        }
        return wa()->getAppPath($path, 'shop');
    }

    protected function preExecute()
    {
        $this->plugin = wa('shop')->getPlugin('sdekint');
        $this->CalcRules = new shopSdekintPluginCalcRulesModel;
        parent::preExecute();
    }

    /**
     * @return array
     */
    protected function compatibleShippingMethods()
    {
        $plugins = (array)(new shopPluginModel)->listPlugins('shipping', ['all' => true]);
        $plugins = array_filter($plugins, function ($p) {
            if ($p['plugin'] !== 'sydsek') {
                return false;
            }
            $info = shopShipping::getPluginInfo((int)$p['id']);
            return version_compare($info['version'], '1.12.0', '>=');
        });

        return $plugins;
    }
}
