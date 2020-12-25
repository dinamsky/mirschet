<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 2.4.0
 * @copyright Serge Rodovnichenko, 2016
 * @license http://www.webasyst.com/terms/#eula Webasyst
 * @package sdekint.controller
 */

/**
 *
 */
class shopSdekintPluginOrderactionsActions extends waJsonActions
{
    /** @var shopSdekintPluginOrderActionsModel */
    protected $OrderActions;

    public function defaultAction()
    {
        $wf = new shopWorkflow();
        $view = wa('shop')->getView();
        $view->assign(array(
            'order_actions'    => $this->OrderActions->getOrderActions(),
            'sdek_states_list' => shopSdekintPluginHelper::listMainCdekStates(),
            'wf_states'        => $wf->getAllStates()
        ));

        $this->response['html'] = $view->fetch($this->getTemplate('Default'));
    }

    /**
     * @throws waException
     */
    public function availableActionsAction()
    {
        $state_id = waRequest::get('state_id', null, waRequest::TYPE_STRING_TRIM);
        if (!$state_id) {
            $this->errors[] = 'Не указан идентификатор статуса';
            return;
        }

        $actions = array();
        foreach (shopSdekintPluginHelper::listWorkflowActionsForState($state_id) as $id => $name) {
            $actions[] = array('id' => $id, 'name' => $name);
        }

        $this->response['actions'] = $actions;
    }

    public function editAction()
    {
        $wf = new shopWorkflow();
        $wf_states = $wf->getAllStates();

        $id = $this->getRequest()->get('id', null, waRequest::TYPE_INT);
        $data = $id ? $this->OrderActions->getById($id) : $this->OrderActions->getEmptyRow();
        $wf_actions = $data ? shopSdekintPluginHelper::listWorkflowActionsForState($data['shop_state']) : [];

        $view = wa('shop')->getView();

        $view->assign([
            'order_actions'    => $this->OrderActions->getOrderActions(),
            'sdek_states_list' => shopSdekintPluginHelper::listMainCdekStates(),
            'wf_state_list'    => array_map(function ($wfs) {
                return $wfs->name;
            }, $wf_states),
            'data'             => $data,
            'wf_actions'       => $wf_actions
        ]);

        $this->response['html'] = $view->fetch($this->getTemplate('Edit'));
    }

    /**
     * Удаление правила
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->post('id', null, waRequest::TYPE_INT);
        if (!$id) {
            $this->errors[] = 'Не указан ID записи для удаления';
            return;
        }

        $result = $this->OrderActions->deleteById($id);
        if (!$result) {
            $this->errors[] = 'Ошибка удаления';
            return;
        }

        $this->response = 'Удалено';
    }

    /**
     * Метод сохнанения новой или обновления существующей записи с правилом
     *
     * @todo: Перенести валидацию в модель
     */
    public function saveAction()
    {
        $data = $this->getRequest()->post('data', null, waRequest::TYPE_ARRAY);

        if (!is_array($data) || empty($data)) {
            $this->errors[] = 'Неверные данные';
            return;
        }

        $data += array('shop_state' => null, 'sdek_state' => null, 'wf_action' => null);
        $data['sdek_state'] = intval($data['sdek_state']);
        if (empty($data['shop_state']) || empty($data['sdek_state']) || empty($data['wf_action'])) {
            $this->errors[] = 'Неверные данные';
            return;
        }


        if (empty($data['id'])) {
            unset($data['id']);
            $cnt = $this->OrderActions->countByField(
                array('shop_state' => $data['shop_state'], 'sdek_state' => $data['sdek_state'])
            );
            if ($cnt) {
                $this->errors[] = 'Правило для указанных статусов в Магазине и СДЭК уже есть';
                return;
            }
            $this->OrderActions->insert($data);
        } else {
            $cnt = $this->OrderActions->countByField('id', $data['id']);
            if (!$cnt) {
                $this->errors[] = 'Ошибка базы данных. Неверный ID правила';
                return;
            }
            $id = $data['id'];
            unset($data['id']);
            $this->OrderActions->updateById($id, $data);
        }

        $this->response = 'Записано';
    }

    protected function preExecute()
    {
        $this->OrderActions = new shopSdekintPluginOrderActionsModel();
        parent::preExecute();
    }

    protected function getTemplate($template = '')
    {
        $path = 'plugins/sdekint/templates/actions/backend/OrderActions';
        if ($template) {
            $path .= '/' . $template . '.html';
        }
        return wa()->getAppPath($path, 'shop');
    }
}
