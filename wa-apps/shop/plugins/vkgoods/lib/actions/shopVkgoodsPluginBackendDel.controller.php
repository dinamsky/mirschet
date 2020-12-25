<?php

class shopVkgoodsPluginBackendDelController extends waLongActionController
{

    protected function preInit()
    {
        $vkgoods = wa()->getPlugin('vkgoods');
        $this->data ['settings'] = $vkgoods->getSettings();

        $this->data ['gid'] = waRequest::post('group_del');
        $this->data ['type_del'] = waRequest::post('type_del');
        $this->data ['aid'] = waRequest::post('album_del');
        $this->data ['mode'] = waRequest::post('del_mode');

        $goods = array();
        $this->data ['vksession'] = $vksession = new shopVkgoodsPluginVkapi ($this->data ['settings'] ['vk_user_id'], $this->data ['settings'] ['token'], false);

        if ($this->data ['type_del'] == 'plugin') {
            $model = new shopVkgoodsPluginProductModel ();
            $tmps = $model->getByField('gid', $this->data ['gid'], true);
            foreach ($tmps as $tmp) {
                $goods[] = array('id' => $tmp['vk_pid']);
            }
        } elseif ($this->data ['type_del'] == 'not_plugin') {
            $model = new shopVkgoodsPluginProductModel();
            $vkproducts = $model->getAll('vk_pid');
            $i = 0;
            $param = array(
                'owner_id' => '-' . $this->data ['gid'],
                'count' => 200
            );
            while (true) {
                $param ['offset'] = $i * 200;
                $response = $vksession->api('market.get', $param, 1);
                if (isset ($response ['error'])) {
                    echo json_encode(array('error' => $response['error']['msg']));
                    break;
                } elseif ($response['response']['count'] > 0 && ifset($response['response']['items'])) {
                    $items = $response ['response']['items'];
                    foreach ($response['response']['items'] as $item) {
                        if (!isset($vkproducts[$item['id']])) {
                            $goods[] = $item;
                        }
                    }
                } else {
                    break;
                }
                $i++;
            }
        } else {
            $i = 0;
            $param = array(
                'owner_id' => '-' . $this->data ['gid'],
                'count' => 200
            );
            if ($this->data ['type_del'] == 'album') {
                $param ['album_id'] = $this->data ['aid'];
            }
            while (true) {
                $param ['offset'] = $i * 200;
                $response = $vksession->api('market.get', $param, 1);
                if (isset ($response ['error'])) {
                    echo json_encode(array('error' => $response['error']['msg']));
                    break;
                } elseif ($response['response']['count'] > 0 && ifset($response['response']['items'])) {
                    $tmp = $response ['response']['items'];
                    if (count($tmp) > 0) {
                        $goods = array_merge($goods, $tmp);
                    } else {
                        break;
                    }
                } else {
                    break;
                }
                $i++;
            }
        }

        if (count($goods) > 0) {
            $this->data ['total'] = count($goods);
            $this->data ['goods'] = $goods;
            return true;
        } else {
            echo json_encode(array('error' => 'Отсутствуют товары для удаления'));
        }

    }

    protected function init()
    {
        $this->data ['count'] = 0;
        $this->data ['done'] = 0;
        $this->data ['errors'] = 0;
        $this->data ['timestamp'] = time();
        $this->data['adone'] = array();
    }

    protected function step()
    {

        for ($part = 1; $part <= 5; $part++) {

            if (!isset($this->data ['goods'] [$this->data ['count']])) {
                break;
            }

            $res = $this->data ['vksession']->api('market.delete', array(
                'item_id' => $this->data ['goods'] [$this->data ['count']] ['id'],
                'owner_id' => '-' . $this->data['gid']
            ), 1);

            if (isset ($res ['error'])) {
                $this->data ['errors']++;
            } else {
                $this->data ['done']++;
                $this->data['adone'][] = $this->data ['goods'] [$this->data ['count']] ['id'];
            }
            $this->data ['count']++;
        }

    }

    protected function info()
    {
        $interval = 0;
        if (!empty ($this->data ['timestamp'])) {
            $interval = time() - $this->data ['timestamp'];
        }
        $response = array(
            'time' => sprintf('%d:%02d:%02d', floor($interval / 3600), floor($interval / 60) % 60, $interval % 60),
            'processId' => $this->processId,
            'progress' => 0.0,
            'ready' => $this->isDone(),
            'total' => $this->data ['total'],
            'errors' => $this->data ['errors'],
            'done' => $this->data ['done']
        );
        $response ['progress'] = ($this->data ['count'] / $this->data ['total']) * 100;
        $response ['progress'] = sprintf('%0.3f%%', $response ['progress']);

        echo json_encode($response);

    }

    protected function isDone()
    {
        return $this->data ['count'] >= $this->data ['total'];
    }

    protected function finish($filename)
    {
        $model = new shopVkgoodsPluginProductModel ();
        $model->query('DELETE FROM `shop_vkgoods_product` WHERE `gid` = ' . $model->escape($this->data['gid']) . ' AND `vk_pid` IN (' . $model->escape(implode(',', $this->data['adone'])) . ')');
        $this->info();
        return true;
    }
}