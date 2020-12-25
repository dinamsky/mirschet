<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version
 * @copyright Serge Rodovnichenko, 2018
 * @license
 */

class shopSdekintPluginCalcRulesModel extends waModel
{
    protected $table = 'shop_sdekint_calc_rules';
    protected $serializable_fields = array('methods', 'courier', 'point', 'conditions');

    public function save($data)
    {
        $id = ifset($data, 'id', null);
        if ($id !== null) {
            $id = (int)$id;
            if (!$id) {
                throw new InvalidArgumentException('Неверный ID');
            }
        }
        unset($data['id']);

        foreach ($this->serializable_fields as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                $data[$key] = serialize($data[$key]);
            }
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
        if (!$result || !is_array($result)) {
            return array();
        }

        return $this->decodeRow($result);
    }

    /**
     * @return waDbResultSelect
     */
    public function queryAllActive()
    {
        return $this->select('*')->where('status > 0')->order('`sort`')->query();
    }

    public function decodeRow(array $row)
    {
        foreach ($this->serializable_fields as $key) {
            if (array_key_exists($key, $row) && is_string($row[$key])) {
                $row[$key] = @unserialize($row[$key]);
            }
        }

        return $row;
    }
}
