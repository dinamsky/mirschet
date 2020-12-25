<?php


class shopTagnavPluginModel extends shopTagModel
{

    public function getByData($id)
    {
        if (is_numeric($id)) {
            return $this->getById($id);
        }

        return $this->getByName($id);
    }

    public function getPrev($tag)
    {
        $where = '';
        if(!$this->getShowEmpty()) {
            $where = ' AND count > 0';
        }
        $prev = $this->where('id < ?'.$where, $tag['id'])->order('id DESC')->limit('1')->fetch();

        if(!$prev) {
            $prev = $this->where('1'.$where)->order('id DESC')->limit('1')->fetch();
        }

        $prev['url'] = $this->getUrl($prev);

        return $prev;
    }

    public function getNext($tag)
    {
        $where = '';
        if(!$this->getShowEmpty()) {
            $where .= ' AND count > 0';
        }
        $next = $this->where('id > ?'.$where, $tag['id'])->order('id')->limit('1')->fetch();
        if(!$next) {
            $next = $this->where('1'.$where)->order('id')->limit('1')->fetch();
        }

        $next['url'] = $this->getUrl($next);

        return $next;
    }

    private function getUrl($tag)
    {
        $routing = wa()->getRouting();

        $url = urlencode($tag['name']);

        if(class_exists('shopTageditorPluginTagModel')) {
            $tageditor_model = new shopTageditorPluginTagModel();

            if($_url = $tageditor_model->where('id=?',$tag['id'])->fetchField('url')) {
                $url = $_url;
            }
        }
        return $routing->getUrl('shop/frontend/tag', array('tag' => $url));
    }

    private function getShowEmpty()
    {
        static $se;
        if($se === null) {
            $plugin = wa('shop')->getPlugin('tagnav');
            $se = (int) $plugin->getSettings('show_empty');
        }
        return $se;
    }
}