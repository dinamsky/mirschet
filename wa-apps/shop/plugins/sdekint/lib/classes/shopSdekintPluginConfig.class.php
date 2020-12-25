<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license Webasyst
 */

use SergeR\CakeUtility\Hash;

/**
 * Class shopSdekintPluginConfig
 */
class shopSdekintPluginConfig
{
    /** @var string */
    protected $file;

    /** @var array|null */
    protected $data = null;

    /**
     * shopSdekintPluginConfig constructor.
     */
    public function __construct()
    {
        $this->file = wa()->getConfig()->getConfigPath('plugins/sdekint/config.php', true, 'shop');
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function get($path)
    {
        if (!$this->isLoaded()) {
            $this->load();
        }

        return Hash::get($this->data, $path);
    }

    public function set($path, $value)
    {
        if (!$this->isLoaded()) {
            $this->load();
        }
        $this->data = Hash::insert($this->data, $path, $value);

        return $this;
    }

    /**
     * @return bool
     */
    public function isExists()
    {
        return file_exists($this->file) && is_file($this->file);
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        return is_readable($this->file);
    }

    /**
     * @return array
     */
    public function load()
    {
        $this->data = [];

        if ($this->isExists() && $this->isReadable()) {
            $this->data = include($this->file);
        }

        return $this->data;
    }

    /**
     * @return bool
     */
    public function isLoaded()
    {
        return $this->data !== null;
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return ($this->isExists() && is_writable($this->file)) || is_writable(dirname($this->file));
    }

    /**
     * @return $this
     */
    public function save()
    {
        if ($this->data === null) {
            return $this;
        }

        if (!$this->isWritable()) {
            throw new RuntimeException('Config file or directory is not writable');
        }

        waUtils::varExportToFile($this->data, $this->file);

        return $this;
    }

    /**
     * @param $id
     * @param array $profile
     * @return array
     */
    public function saveSenderProfile($id, array $profile)
    {
        if (!$this->isLoaded()) {
            $this->load();
        }

        $profiles = $this->getSenderProfiles();

        if (!$id) {
            $id = waString::uuid();
            $profiles[] = ['id' => $id] + $profile;
        } else {
            array_walk($profiles, function (&$p, $key, $_p) {
                if ($p['id'] === $_p['id']) {
                    $p = $_p;
                } elseif ($_p['is_default']) {
                    $p['is_default'] = false;
                }
            }, ['id' => $id] + $profile);
        }

        $this->data = Hash::insert($this->data, 'sender.profiles', $profiles);

        return ['id' => $id] + $profile;
    }

    /**
     * @return array
     */
    public function getSenderProfiles()
    {
        return (array)$this->get('sender.profiles');
    }

    /**
     * @return array
     */
    public function getConsolidation()
    {
        if (!$this->isLoaded()) {
            $this->load();
        }

        return ((array)Hash::get($this->data, 'consolidation')) + ['city_id' => null, 'pvz_id' => null];
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setConsolidation(array $data)
    {
        if (!$this->isLoaded()) {
            $this->load();
        }
        $this->data = Hash::insert($this->data, 'consolidation', $data + ['city_id' => null, 'pvz_id' => null]);

        return $this;
    }
}