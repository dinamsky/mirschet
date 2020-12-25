<?php

class shopWtspPlugin extends shopPlugin
{
    public function backendOrder($param){
        $settings =  wa('shop')->getPlugin("wtsp")->getSettings();
        $settings = isset($settings['settings']) ? $settings['settings'] : array("whatsapp" => 1, "viber" => 1);

        $phone = preg_replace('/[^0-9]/', '', $param['contact']['phone']);

        $content_link = "";

        //action_link

        if( isset($settings['whatsapp'])){
            $content_link.= '<a href="#" class="wtsp-link"><i class="icon16"></i><i class="icon16"></i>';
            $content_link.= 'Написать в WhatsApp';
            $content_link.= '</a>';
        }
        if( isset($settings['viber'])){
            $content_link.= '<a href="#" class="wtsp-viber-link"><i class="icon16"></i><i class="icon16"></i>';
            $content_link.= 'Написать в Viber';
            $content_link.= '</a>';
        }
        if( isset($settings['telegram'])){
            $content_link.= '<a href="#" class="wtsp-telegram-link"><i class="icon16 telegram"></i>';
            $content_link.= 'Написать в Telegram';
            $content_link.= '</a>';
        }
        if( isset($settings['skype'])){
            $content_link.= '<a href="#" class="wtsp-skype-link"><i class="icon16 skype"></i>';
            $content_link.= $s_html.'Написать в Skype'.$s_html;
            $content_link.= '</a>';
        }
        /*if( isset($settings['vk'])){
            $content_link.= '<a href="#" class="wtsp-vkontakte-link"><i class="icon16 vkontakte"></i>';
            $content_link.= $s_html.'Написать в Vk'.$s_html;
            $content_link.= '</a>';
        }
        if( isset($settings['facebook'])){
            $content_link.= '<a href="#" class="wtsp-skype-link"><i class="icon16 facebook"></i>';
            $content_link.= $s_html.'Написать в Facebook'.$s_html;
            $content_link.= '</a>';
        }*/
        $view = wa()->getView();

	    $view->assign('phone', $phone );
        $view->assign('order_id', $param['id'] );
        $view->assign('wtsp_plugin', (int) waRequest::cookie('wtsp_plugin') );

        $template_path = wa()->getAppPath('plugins/wtsp/templates/backendOrder.html',  'shop');
		$html = $view->fetch($template_path);



        // return
        return array('action_link' => $content_link, 'info_section' => $html);
    }
    public function backendOrdersFiles(){
        $this->addJs('js/wtsp.js');
        $this->addCss('css/wtsp.css');
    }
}
