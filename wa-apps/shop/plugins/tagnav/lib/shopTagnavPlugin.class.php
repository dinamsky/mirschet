<?php

class shopTagnavPlugin extends shopPlugin
{

    public function frontendSearch($params)
    {
        $r = wa()->getRequest();
        if($r->param('action') == 'tag') {
            $view = wa()->getView();
            $id = $r->param('tag');

            $tag_model = new shopTagnavPluginModel();

            if ($tag = $tag_model->getByData($id)) {

                if($prev = $tag_model->getPrev($tag)) {
                    $view->assign('prev_tag', $prev);
                }

                if($next = $tag_model->getNext($tag)) {
                    $view->assign('next_tag', $next);
                }

                if($this->getSettings('show_at_hook')) {
                    return $view->fetch($this->path.'/templates/hooks/frontendSearch.html');
                }
                
            }
        }
        return '';
    }
}
