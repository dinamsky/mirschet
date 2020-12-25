<?php

/**
 * Created by PhpStorm.
 * User: snark | itfrogs.ru
 * Date: 1/10/16
 * Time: 2:44 AM
 */
class shopVkshopPluginAlbumsModel extends waModel
{
    /**
     * @var string
     */
    protected $table = 'shop_vkshop_albums';

    /**
     * @param $group_id
     * @return array
     */
    public function getMainAlbum($group_id)
    {
        $album = $this->query('SELECT * FROM shop_vkshop_albums WHERE type = "main" AND group_id = i:group_id',
            array(
                'group_id' => $group_id,
            )
        )->fetchAssoc();
        return $album;
    }

    /**
     * @param $set_id
     * @return array
     */
    public function getAlbumBySetId($set_id)
    {
        $album = $this->query(
            'SELECT s.*, a.album_id FROM shop_set s '
            . ' LEFT JOIN shop_vkshop_albums a ON (s.id = a.shop_id AND a.type = s:type) '
            . ' WHERE s.id = s:set_id',
            array(
                'set_id' => $set_id,
                'type' => 'set',
            )
        )->fetchAssoc();
        return $album;
    }

    /**
     * @param $type_id
     * @return array
     */
    public function getAlbumByTypeId($type_id)
    {
        $album = $this->query(
            'SELECT t.*, a.album_id FROM shop_type t '
            . ' LEFT JOIN shop_vkshop_albums a ON (t.id = a.shop_id AND a.type = s:type) '
            . ' WHERE t.id = i:type_id',
            array(
                'type_id' => $type_id,
                'type' => 'type',
            )
        )->fetchAssoc();
        return $album;
    }

    /**
     * @param $group_id
     * @return array
     */
    public function getAlbumsByGroupId($group_id)
    {
        $albums = $this->query('SELECT * FROM shop_vkshop_albums WHERE  group_id = i:group_id',
            array(
                'group_id' => $group_id,
            )
        )->fetchAll();
        return $albums;
    }
}
