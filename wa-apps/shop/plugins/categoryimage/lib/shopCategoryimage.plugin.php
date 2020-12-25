<?php

/**
 * @author Плагины Вебасист <info@wa-apps.ru>
 * @link http://wa-apps.ru/
 */
class shopCategoryimagePlugin extends shopPlugin
{
    protected static $cache = null;

    public function categoryDialog($category)
    {
        $view = wa()->getView();
        $view->assign('category', $category);
        if ($category['image']) {
            $view->assign('image_url', self::getImageUrl($category, '96'));
        }
        return $view->fetch($this->path.'/templates/Dialog.html');
    }

    public function categoryTitle($params)
    {
        if ($params && $params['type'] == 'category' && $params['info']['image']) {
            $view = wa()->getView();
            $view->assign('image_url', self::getImageUrl($params['info'], '96'));
            return array(
                'title_suffix' => $view->fetch($this->path.'/templates/Title.html')
            );
        }
    }

    public static function getImageUrlById($category_id, $size = '', $default_url = '')
    {
        if (is_null(self::$cache)) {
            // сохраняем в кэш все картинки всех категорий, чтобы при вызове в цикле не было лишних запросов
            self::$cache = array();
            $category_model = new shopCategoryModel();
            $sql = 'SELECT id, image FROM '.$category_model->getTableName().' WHERE image IS NOT NULL';
            foreach ($category_model->query($sql) as $row) {
                if (!empty($row['image'])) {
                    self::$cache[$row['id']] = $row['image'];
                }
            }
        }
        if (!empty(self::$cache[$category_id])) {
            $path = 'categories/'.$category_id.'/'.$category_id.($size ? '.'.$size : '').self::$cache[$category_id];
            $file_path = wa()->getDataPath($path, true);
            return wa()->getDataUrl($path, true).(file_exists($file_path) ? '?v='.filemtime($file_path) : '');
        }
        return $default_url;
    }

    public static function getImageUrl($c, $size = '', $default_url = '')
    {
        if (empty($c['image'])) {
            return $default_url;
        }
        $path = 'categories/'.$c['id'].'/'.$c['id'].($size ? '.'.$size : '').$c['image'];
        $file_path = wa()->getDataPath($path, true);
        return wa()->getDataUrl($path, true).(file_exists($file_path) ? '?v='.filemtime($file_path) : '');
    }

    public function categoryDelete($category)
    {
        if (is_numeric($category)) {
            $category_model = new shopCategoryModel();
            $category = $category_model->getById($category);
        }

        if ($category && !empty($category['image'])) {
            $path = wa()->getDataPath('categories/'.$category['id'], true, 'shop', false);
            waFiles::delete($path);
        }
    }

    public function categorySave($category)
    {
        $is_save = false;
        if (waRequest::get('module') == 'category' && waRequest::get('action') == 'save') {
            $is_save = true;
        } elseif (waRequest::get('module') == 'products' && waRequest::get('action') == 'saveListSettings') {
            $is_save = true;
        }
        if (!$is_save) {
            return;
        }
        $category_model = new shopCategoryModel();
        if (!waRequest::post('image')) {
            $category_model->updateById($category['id'], array('image' => ''));
            waFiles::delete(wa()->getDataPath('categories/'.$category['id'], true, 'shop', false));
        }

        $image = waRequest::file('image_file');
        if ($image->uploaded() && in_array(strtolower($image->extension), array('jpg', 'jpeg', 'png', 'gif'))) {
            $category['image'] = '.'.$image->extension;
            $path = wa()->getDataPath('categories/'.$category['id'].'/', true, 'shop');
            $image->moveTo($path, $category['id'].$category['image']);
            $sizes = $this->getSettings('sizes');
            if (!$sizes) {
                $sizes = '96';
            }
            $sizes = explode(';', $sizes);
            foreach ($sizes as $size) {
                if ($thumb_img = shopImage::generateThumb($path.$category['id'].$category['image'], $size)) {
                    $thumb_img->save($path.$category['id'].'.'.$size.$category['image']);
                }
            }
            $category_model->updateById($category['id'], array('image' => $category['image']));
        }
    }
}
