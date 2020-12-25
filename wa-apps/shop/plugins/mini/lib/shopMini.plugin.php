<?php
class shopMiniPlugin extends shopPlugin {
    public function frontendHeader(){
        if( wa()->appExists('announce') ) {
            wa('announce');
            $v = new announceViewHelper();
            return $v->group(1);
        }
    }
}