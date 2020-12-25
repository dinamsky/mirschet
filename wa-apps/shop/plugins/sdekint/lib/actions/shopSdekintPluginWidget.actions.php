<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

class shopSdekintPluginWidgetActions extends waJsonActions
{
    /** @var shopSdekintPlugin */
    protected $plugin;

    /** @var shopSdekintPluginWidgetSettingsModel */
    protected $WidgetSettings;

    public function defaultAction()
    {
//        $per_page = 2; // for paginator testing
        $per_page = max(2, (int)$this->getConfig()->getOption('products_per_page'));
        if (!$per_page) {
            $per_page = 10;
        }
        $pager = $this->plugin->helper->paginator(
            (int)waRequest::request('page', 1, waRequest::TYPE_INT),
            (int)$this->WidgetSettings->countAll(),
            $per_page
        );

        $shipping_methods = $this->compatibleShippingMethods();

        $data = array_values((array)$this->WidgetSettings
            ->select('*')
            ->order('id')
            ->limit((string)ifempty($pager, 'limit', 0, 1) . ',' . (string)ifempty($pager, 'limit', 1, $per_page))
            ->fetchAll());
        $pagination = $this->plugin->helper->pagination($pager + ['mode' => 'hash', 'url_before' => '#widgetcontrol/']);

        array_walk($data, function (&$item) use ($shipping_methods) {
            if (is_array($item) && array_key_exists('settings', $item)) {
                $settings = @unserialize($item['settings']);
                if (!is_array($settings)) {
                    $settings = array();
                }
                $method_id = (int)ifset($settings, 'method', 0);
                $method = array('id' => $method_id);
                foreach ($shipping_methods as $m) {
                    if ((int)$m['id'] === $method_id) {
                        $method = $m;
                        break;
                    }
                }
                $settings['method'] = $method;
                $item['settings'] = $settings;
            }
        });

        $view = wa('shop')->getView();
        $view->assign('pagination', $pagination);
        $view->assign('data', $data);
        $this->response['html'] = $view->fetch($this->getTemplate('Default'));
    }

    /**
     * Delete widget config
     */
    public function deleteAction()
    {
        $id = (int)$this->getRequest()->post('id', 0, waRequest::TYPE_INT);
        if (!$id) {
            $this->errors[] = ['Invalid ID', 500];
            return;
        }
        try {
            $this->WidgetSettings->deleteById($id);
            $this->response = 'deleted';
        } catch (waException $e) {
            $this->errors[] = [$e->getMessage(), $e->getCode()];
        }

        return;
    }

    /**
     * Edit/Add/Show widget config
     */
    public function editAction()
    {
        $view = wa('shop')->getView();
        $id = $this->getRequest()->get('id', null, waRequest::TYPE_INT);
        $widget = array();
        if ($id) {
            $widget = $this->WidgetSettings->findById($id);
        }
        $view->assign('shipping_methods', $this->compatibleShippingMethods());
        $view->assign('widget', $widget);
        $this->response['html'] = $view->fetch($this->getTemplate('Edit'));
    }

    /**
     * @throws waException
     */
    public function saveAction()
    {
        if ($this->getRequest()->method() !== 'post') {
            throw new waException('Invalid method', 403);
        }
        $data = $this->getRequest()->post('widget', array(), waRequest::TYPE_ARRAY);
        if (empty($data['id'])) {
            unset($data['id']);
        }

        try {
            $id = (new shopSdekintPluginWidgetSettingsModel)->save($data);
            $this->response['id'] = $id;
        } catch (Exception $e) {
            $this->errors[] = array($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param string $template
     * @return string
     */
    protected function getTemplate($template = '')
    {
        $path = 'plugins/sdekint/templates/actions/backend/Widget';
        if ($template) {
            $path .= '/' . $template . '.html';
        }
        return wa()->getAppPath($path, 'shop');
    }

    /**
     * @throws waException
     */
    protected function preExecute()
    {
        $this->plugin = wa('shop')->getPlugin('sdekint');
        $this->WidgetSettings = new shopSdekintPluginWidgetSettingsModel;
        parent::preExecute();
    }

    /**
     * @return array
     */
    protected function compatibleShippingMethods()
    {
        $plugins = (array)(new shopPluginModel)->listPlugins('shipping');
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
