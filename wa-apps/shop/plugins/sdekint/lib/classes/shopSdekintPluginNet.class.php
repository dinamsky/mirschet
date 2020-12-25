<?php

/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version
 * @copyright Serge Rodovnichenko, 2017
 * @license
 */
class shopSdekintPluginNet extends waNet
{
    public function __construct($options = array(), $custom_headers = array())
    {
        if (empty($options['format'])) {
            $options['format'] = waNet::FORMAT_XML;
        }

        if (empty($options['request_format'])) {
            $options['request_format'] = 'default';
        }

        parent::__construct($options, $custom_headers);

    }

    public function setFormat($format, $request_format = null, $mime = null)
    {
        if (!$request_format) {
            $request_format = $format;
        }

        $this->options['format'] = $format;
        $this->options['request_format'] = $request_format;

        $this->request_headers['Accept'] = $mime;

        return $this;
    }

    /**
     * Overloaded because Accept header is hardcoded and we need an application/xml mime-type
     *
     * @param $transport
     * @param bool|true $raw
     * @return array
     */
    protected function buildHeaders($transport, $raw = true)
    {
        $this->request_headers['Connection'] = 'close';
        $this->request_headers['Date'] = date('c');
        switch ($this->options['format']) {
            case self::FORMAT_JSON:
                $this->request_headers["Accept"] = "application/json";
                break;

            case self::FORMAT_XML:
                $this->request_headers["Accept"] = "text/xml,application/xml";
                break;

            default:
                $this->request_headers['Accept'] = '*/*';
                break;
        }

        $this->request_headers['Accept-Charset'] = $this->options['charset'];

        /**
         * Accept
         * | Accept-Charset           ; Section 14.2
         * | Accept-Encoding          ; Section 14.3
         * | Accept-Language          ; Section 14.4
         * | Authorization            ; Section 14.8
         * | Expect                   ; Section 14.20
         * | From                     ; Section 14.22
         * | Host                     ; Section 14.23
         * | If-Match                 ; Section 14.24
         */

        if (!empty($this->options['authorization'])) {
            $authorization = sprintf("%s:%s", $this->options['login'], $this->options['password']);
            $this->request_headers["Authorization"] = "Basic " . urlencode(base64_encode($authorization));
        }

        $this->request_headers['User-Agent'] = $this->user_agent;
        $this->request_headers['X-Framework-Method'] = $transport;

        if ($raw) {
            return $this->request_headers;
        } else {
            $headers = array();
            foreach ($this->request_headers as $header => $value) {
                $headers[] = sprintf('%s: %s', $header, $value);
            }
            return $headers;
        }
    }
}
