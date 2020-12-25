<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

class shopSdekintPluginWidgetSettingsModel extends waModel
{
    protected $table = 'shop_sdekint_widget_settings';

    public function save($data)
    {
        $id = ifset($data, 'id', null);
        if ($id !== null) {
            $id = (int)$id;
            if (!$id) {
                throw new InvalidArgumentException('ID must be positive integer');
            }
        }

        unset($data['id']);

        if (array_key_exists('settings', $data)) {
            $data['settings'] = serialize($data['settings']);
        }

        if ($id) {
            $this->updateById($id, $data);
        } else {
            $id = $this->insert($data);
        }

        return $id;
    }

    /**
     * @param int $id
     * @return array
     */
    public function findById($id)
    {
        $result = $this->getById($id);

        if ($result && is_array($result) && array_key_exists('settings', $result)) {
            $settings = @unserialize($result['settings']);
            if ($settings !== false) {
                $result['settings'] = $settings;
            }
        }

        return (array)$result;
    }
}
