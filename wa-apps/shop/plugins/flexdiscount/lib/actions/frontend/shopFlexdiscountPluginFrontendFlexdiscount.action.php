<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class shopFlexdiscountPluginFrontendFlexdiscountAction extends shopFrontendAction
{

    private static $page_name;

    public function execute()
    {
        $settings = shopFlexdiscountHelper::getSettings();

        // Заголовок страницы
        self::$page_name = $title = (!empty($settings['flexdiscount_my_discounts']['page_name']) ? shopFlexdiscountHelper::escape($settings['flexdiscount_my_discounts']['page_name']) : _wp('Your discounts'));
        $this->view->assign('title', $title);

        waRequest::setParam('plugin', 'flexdiscount');

        $html = '';
        // Список доступных скидок
        if (!empty($settings['my_discounts']) && !empty($settings['flexdiscount_my_discounts']['value'])) {
            $view = wa()->getView();
            
            if (!empty($settings['flexdiscount_my_discounts']['show_only_active'])) {
                $workflow = shopFlexdiscountPluginHelper::getProductDiscounts(shopFlexdiscountData::getAbstractProduct(true), null, 0, false);
                $discounts = $workflow['items'];
            } else {
                $discounts = shopFlexdiscountPluginHelper::getAvailableDiscounts(null, null, 0, array(), false);
            }

            $view->assign(array(
                'fl_discounts' => $discounts,
                'view_type' => !empty($settings['flexdiscount_my_discounts']['type']) ? $settings['flexdiscount_my_discounts']['type'] : ''
            ));
            $html .= $view->fetch('string:' . $settings['my_discounts']);
            $view->clearAssign(array('fl_discounts', 'view_type'));
        }

        $this->view->assign('plugin_content', $html);
        $this->view->assign('show_nav', !empty($settings['flexdiscount_my_discounts']['show_nav']));
        $this->view->assign('show_nav_above', !empty($settings['flexdiscount_my_discounts']['show_nav_pos']));

        // Выводим страницу со скидками через файл текущей темы my.flexdiscount.html
        $view = wa()->getView();
        $theme = new waTheme(waRequest::getTheme());
        $theme_path = $theme->getPath();
        $f = 'my.flexdiscount.html';
        $template_path = $theme_path . '/' . $f;
        if (file_exists($template_path)) {
            $view->setThemeTemplate($theme, $f);
            $this->setTemplate($template_path);
        }

        if (!waRequest::isXMLHttpRequest()) {
            $this->setLayout(new shopFrontendLayout());
            $this->getResponse()->setTitle($title);
            $this->view->assign('breadcrumbs', self::getBreadcrumbs());
            $this->layout->assign('nofollow', true);
        }
    }

    public static function getBreadcrumbs()
    {
        return array(
            array(
                'name' => self::$page_name,
                'url' => wa()->getRouteUrl('/frontend/my') . 'flexdiscount/',
            ),
        );
    }

}
