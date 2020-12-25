<?php

class shopVkontaktePluginBackendSendphotosController extends waJsonController
{
    public function execute()
    {
        $product_id = waRequest::request('product_id', 0, 'int');
        $upload_url = urldecode(waRequest::request('upload_url'));

        $product = new shopProduct($product_id);

        $images = $product->getImages();

        $photos = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: multipart/form-data"));
        curl_setopt($ch, CURLOPT_URL, $upload_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
//        $verbose = fopen('php://temp', 'rw+');
//        curl_setopt($ch, CURLOPT_STDERR, $verbose);


        $q = 1;
        foreach ($images AS $image) {
            $im = new shopProductImagesModel();
            $image = $im->getById($image['id']);
            $imagePath = shopImage::getPath($image);

            if ($q > 5) {
                break;
            }

            $photos['file' . $q] = self::getCurlFile($imagePath);
            $q++;
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $photos);

        $result = curl_exec($ch);

        /*
        if ($result === FALSE) {
            printf("cUrl error (#%d): %s<br>\n", curl_errno($ch),
                htmlspecialchars(curl_error($ch)));
        }

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
        */

        curl_close($ch);
        $this->response = json_decode($result);
    }

    private static function getCurlFile($filename)
    {
        if (class_exists('CURLFile')) {
            $image = new waImage($filename);
            $file = new CURLFile($filename, $image->type);
            return $file;
        }
        return '@'.$filename;
    }
}