<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 2.4.0
 * @copyright Serge Rodovnichenko, 2016
 * @license http://www.webasyst.com/terms/#eula Webasyst
 * @package sdekint.model
 */

/**
 * shopSdekintPluginOrderActionsModel
 */
class shopSdekintPluginOrderActionsModel extends waModel
{
    protected $table = 'shop_sdekint_order_actions';

    /**
     * Читает из БД и готовит список действий над заказами
     *
     * @return array
     */
    public function getOrderActions()
    {
        $actions = $this->order('id')->fetchAll();
        $wf = new shopWorkflow();
        $cdek_states = shopSdekintPluginHelper::listMainCdekStates();
        foreach ($actions as &$action) {
            $state = null;
            $shop_state = array('id' => $action['shop_state'], 'state' => null);
            try {
                $state = $wf->getStateById($action['shop_state']);
                if ($action['shop_state'] !== $state->id) {
                    throw new waException();
                }
                $shop_state['state'] = $state;
            } catch (waException $e) {
                $shop_state = null;
            }
            $action['shop_state'] = $shop_state;

            $sdek_state = array(
                'id'=>$action['sdek_state'],
                'name' => ifempty($cdek_states, $action['sdek_state'], null)
            );
            $action['sdek_state']=$sdek_state;

            $wf_action = array('id'=>$action['wf_action'], 'action'=>null, 'allowed'=>false);
            $wfa = $wf->getActionById($action['wf_action']);
            if ($wfa) {
                $wf_action['action'] = $wfa;

                if ($state) {
                    $allowed_actions = $state->getActions(null, true);
                    $wf_action['allowed'] = array_key_exists($action['wf_action'], $allowed_actions);
                }

            }
            $action['wf_action']=$wf_action;
        }
        unset($action);

        return $actions;
    }
}
