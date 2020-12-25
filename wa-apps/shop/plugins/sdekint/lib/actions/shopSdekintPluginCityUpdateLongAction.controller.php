<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

/**
 * Class shopSdekintPluginCityUpdateLongActionController
 */
class shopSdekintPluginCityUpdateLongActionController extends waLongActionController
{
    /** @var shopSdekintPlugin */
    protected $plugin;

    /** @var shopSdekintPluginCityModel */
    protected $City;

    /**
     * @return void
     */
    protected function preExecute()
    {
        $this->getResponse()
            ->addHeader('Content-type', 'application/json')
            ->sendHeaders();

        try {
            $this->plugin = wa('shop')->getPlugin('sdekint');
        } catch (waException $e) {
            echo waUtils::jsonEncode(array('error' => [$e->getMessage(), $e->getCode()]));
            exit;
        }

        $this->City = new shopSdekintPluginCityModel();
    }

    /**
     * Initializes new process.
     * Runs inside a transaction ($this->data and $this->fd are accessible).
     */
    protected function init()
    {
        $this->data['file_path'] = wa('shop')->getConfig()->getPluginPath('sdekint') . '/lib/config/data/shop_sdekint_cities.csv';

        if (!file_exists($this->data['file_path']) || !is_file($this->data['file_path']) || !is_readable($this->data['file_path'])) {
            $message = 'Не удалось открыть на чтение файл \'' . $this->data['file_path'] . '\'';
            $this->plugin->helper->log($message, true);
            echo waUtils::jsonEncode(array('error' => ['Не удается прочесть файл с реестром городов', 1]));
            exit;
        }

        $this->data['info']['filesize'] = filesize($this->data['file_path']);
        if ($this->data['info']['filesize'] === false) {
            $message = 'Не удалось определить размер файла \'' . $this->data['file_path'] . '\'';
            $this->plugin->helper->log($message, true);
            echo waUtils::jsonEncode(array('error' => ['Не удается прочесть файл с реестром городов', 1]));
            exit;
        }

        $fh = fopen($this->data['file_path'], 'r');
        if ($fh === false) {
            $message = 'Не удалось открыть (fopen) на чтение файл \'' . $this->data['file_path'] . '\'';
            $this->plugin->helper->log($message, true);
            echo waUtils::jsonEncode(array('error' => ['Не удается прочесть файл с реестром городов', 1]));
            exit;
        }

        $header = fgetcsv($fh, 1024, ';');
        if ($header === false) {
            $message = 'Не удалось прочесть заголовок файла';
            $this->plugin->helper->log($message, true);
            echo waUtils::jsonEncode(array('error' => ['Не удалось прочесть заголовок файла', 1]));
            exit;
        }
        $header = array_flip($header);
        $this->data['map'] = $header;

        $this->data['record_count'] = 0;

        $this->data['offset'] = ftell($fh);
        $this->data['done'] = false;

        $this->City->truncate();

    }

    /**
     * Checks if there is any more work for $this->step() to do.
     * Runs inside a transaction ($this->data and $this->fd are accessible).
     *
     * $this->getStorage() session is already closed.
     *
     * @return boolean whether all the work is done
     */
    protected function isDone()
    {
        return $this->data['done'];
    }

    /**
     * Performs a small piece of work.
     * Runs inside a transaction ($this->data and $this->fd are accessible).
     * Should never take longer than 3-5 seconds (10-15% of max_execution_time).
     * It is safe to make very short steps: they are batched into longer packs between saves.
     *
     * $this->getStorage() session is already closed.
     * @return boolean false to end this Runner and call info(); true to continue.
     */
    protected function step()
    {
        $fh = fopen($this->data['file_path'], 'r');
        fseek($fh, $this->data['offset']);
        $rows = array();
        $row = false;

        for ($i = 0; $i < 50 && ($row = fgetcsv($fh, 1024, ';')) !== false; $i++) {
            $data_row = array();
            foreach ($this->data['map'] as $field => $index) {
                if (isset($row[$index])) {
                    $data_row[$field] = $row[$index];
                    if (($field == 'pod_max') && ($data_row[$field] == '')) {
                        $data_row[$field] = null;
                    }
                }
            }
            if (!empty($data_row)) {
                $rows[] = $data_row;
            }
        }

        try {
            if (!empty($rows)) {
                $this->City->multipleInsert($rows);
            }

            $this->data['offset'] = ftell($fh);
            $this->data['done'] = ($row === false);
            $this->data['record_count'] += count($rows);
        } catch (waException $e) {
            $this->plugin->helper->log('Ошибка записи в базу данных (multipleInsert) при обновлении реестра городов', true);
        }
        fclose($fh);

        return !$this->data['done'] && (!$this->max_exec_time || ($this->remaining_exec_time > 10));
    }

    /**
     * Called when $this->isDone() is true
     * $this->data is read-only, $this->fd is not available.
     *
     * $this->getStorage() session is already closed.
     *
     * @param $filename string full path to resulting file
     * @return boolean true to delete all process files; false to be able to access process again.
     */
    protected function finish($filename)
    {
        $cleanup = !!$this->getRequest()->post('cleanup');
        if (!$cleanup) {
            $this->info();
        }
        return $cleanup;
    }

    /** Called by a Messenger when the Runner is still alive, or when a Runner
     * exited voluntarily, but isDone() is still false.
     *
     * This function must send $this->processId to browser to allow user to continue.
     *
     * $this->data is read-only. $this->fd is not available.
     */
    protected function info()
    {
        $response = array(
            'processId'    => $this->processId,
            'ready'        => $this->isDone(),
            'progress'     => '0.0%',
            'record_count' => $this->data['record_count']
        );

        $response['progress'] = sprintf('%0.1F%%', max(1, $this->data['offset'] * 100 / $this->data['info']['filesize']));

        echo waUtils::jsonEncode($response);
    }
}
