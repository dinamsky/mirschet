<?php

class siteAdvancedparamsPluginBackendFileUploadController extends waJsonController {

    // Разрешенные мим типы и расширения
    protected $access_mime_types = array(
        'txt' => 'text/plain',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',

        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'mov' => 'video/quicktime',
        'qt' => 'video/quicktime',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',

        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',

        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
    );
    public function execute() {
        if(waRequest::method()=='post') {
            // Принимаем данные
            $action = waRequest::post('action','', waRequest::TYPE_STRING_TRIM);
            $action_id = waRequest::post('action_id',0, waRequest::TYPE_INT);
            $field_name = waRequest::post('field_name','', waRequest::TYPE_STRING_TRIM);
            // Проверяем существование типа экшена
            if(!siteAdvancedparamsPlugin::actionExists($action)) {
                $this->errors[] = 'Неверный тип экшена!';
                return;
            }
            // Проверяем идентификатор экшена
            if(empty($action_id)) {
                $this->errors[] = 'Неверный идентификатор экшена!';
                return;
            }
            $field_model = new siteAdvancedparamsFieldModel();
            $field = $field_model->getFieldByName($action, $field_name);
            // Проверяем существование поля у экшена
            if(!$field || !siteAdvancedparamsPlugin::isFileType($field['type'])) {
                $this->errors[] = 'Поле не существует!';
                return;
            }
            // Если все проверки успешны, добавляем файл
            $url = waRequest::post('url' ,'',waRequest::TYPE_STRING_TRIM);
            $file = waRequest::file('file');
            if(!empty($url) || $file->uploaded()) {
                $size = array();
                if ($field['type'] == 'image') {
                    $size['type'] = waRequest::post('size_type', 'none', waRequest::TYPE_STRING_TRIM);
                    $size['width'] = waRequest::post('width', 0, waRequest::TYPE_INT);
                    $size['height'] = waRequest::post('height', 0, waRequest::TYPE_INT);
                }
                if (!empty($url)) {
                    $file_data = $this->downloadFile($url);
                    if($file_data) {
                        $file = new waRequestFile($file_data, true);
                    } else {
                        return;
                    }
                }
                $files_class = new siteAdvancedparamsPluginFiles($action, $action_id);
                $file_link = $files_class->saveFile($file, $field['type'], $field['name'], $this->errors, $size);
                if (!$file_link) {
                    return;
                } else {
                    $this->response['file_link'] = $file_link;
                    $this->response['field_name'] = $field_name;
                    $this->response['type'] = $field['type'];
                    $this->response['file_name'] = basename($file_link);
                }
            } else {
                $this->errors[] = 'Файл не был загружен!';
            }
        } else {
            $this->errors[] = 'Файл не был загружен!';
        }
    }
    /*
     * Загружает файл по ссылке
     * return false|array data
     * */
    private function downloadFile($url) {
        $Curl = new siteAdvancedparamsPluginCurl();
        $Curl->agent = "Mozilla/5.0 (Windows; U; Windows NT 6.1; uk; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13 Some plugins";
        $domain = $Curl->cutDomain($url);
        $Curl->referer = "http://$domain/index.php";
        $Curl->rawheaders["Host"] = $domain;
        $Curl->maxredirs = 2;
        $Curl->fetch($url);
        $file_content = $Curl->results;
        // Если есть контент файла
        if(!empty($file_content)) {
            if(is_writeable(sys_get_temp_dir())) {
                $tmp_dir = sys_get_temp_dir();
            } elseif(is_writeable(ini_get("upload_tmp_dir"))) {
                $tmp_dir = ini_get("upload_tmp_dir");
            } else {
                $tmp_dir =  wa()->getDataPath('advancedparams', true, 'site');
            }
            $tmpfname = $tmp_dir.DIRECTORY_SEPARATOR.'advancedparams.uploadfile.temp';
            $this->file_put_contents($tmpfname, '');
            if(file_exists($tmpfname)) {

                if($this->file_put_contents($tmpfname, $file_content)) {
                    $file_data = array();
                    $file_data['mime_type'] = 'none';
                    $url_arr = parse_url($url);
                    $file_data['name'] =  basename($url_arr['path']);
                    foreach($Curl->headers as $header) {
                        if(preg_match('/^content-type:[\s]+(.+)/i', $header, $match)) {
                            $file_data['mime_type'] = $match[1];
                        }
                    }
                    $file_data['tmp_filename'] = $tmpfname;
                    return $this->getFileData($file_data);
                } else {
                    $this->errors[] = 'Не удалось записать данные в временный файл ('.$tmpfname.')!';
                    return false;
                }
            } else {
                $this->errors[] = 'Не удалось создать временный файл: '.$tmpfname.'!';
                return false;
            }
        } else {
            var_dump($Curl->error);
            $this->errors[] = 'Не удалось закачать файл по ссылке!';
            return false;
        }
    }
    // Подготавливает массив данных файла для waRequestFile
    protected function getFileData($file_data) {
        $data = array(
            'name' => '',
            'type' => 'file',
            'size' => filesize($file_data['tmp_filename']),
            'tmp_name' =>  $file_data['tmp_filename'],
            'error' => UPLOAD_ERR_OK,
        );
        // ПРоверяем имя
        $info = pathinfo($file_data['name']);
        $ext = false;
        if(isset($info['extension'])) {
            $ext = $info['extension'];
        }
        if(!array_key_exists($ext, $this->access_mime_types) ) {
            if (in_array($file_data['mime_type'], $this->access_mime_types)) {
                $ext = array_search($file_data['mime_type'], $this->access_mime_types);
                $data['name'] = pathinfo($file_data['name'], PATHINFO_FILENAME).'.'.$ext;
            } else {
                $this->errors[] = 'Файл с таким расширением не поддерживается!';
                return false;
            }
        } else {
            $data['name'] = $file_data['name'];
        }
        return $data;
    }
    /*
    * Записывает строку в файл
    * return bool
    * */
    private function file_put_contents($filename, $content) {
        $write = false;
        if(function_exists('file_put_contents')) {
            $write = file_put_contents($filename, $content);
        } else {
            $fp = fopen($filename, "w");
            if($fp) {
                $write = fwrite($fp, $content);
                fclose($fp);
            }
        }
        return $write;
    }
}