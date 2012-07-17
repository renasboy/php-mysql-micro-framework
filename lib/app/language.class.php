<?php
namespace app;

class language {

    private $_conf  = null;
    private $_data  = [];

    public function __construct ($lang, conf $conf) {
        $this->_conf = $conf;
        $this->_lang = $this->_resolve($lang);
    }

    private function _init () {
        if ($this->_data === []) {
            $file = sprintf('%s/%s.ini', $this->_conf->get('conf_language'), $this->_lang);
            if (!file_exists($file)) {
                // throw 500 since we do not have error
                throw new \Exception('Lang file does not exist', 500);
            }
            $this->_data = parse_ini_file($file, true);
        }
    }

    public function get ($key = null, $multi = true) {
        $this->_init();
        if ($key === null) {
            return $this->_data;
        }
        if ($multi && strpos($key, '.')) {
            list($section, $key) = explode('.', $key);
            if (array_key_exists($section, $this->_data) &&
                array_key_exists($key, $this->_data[$section])) {
                return $this->_data[$section][$key];
            }
        }
        else if (array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        }
        return null;
    }


    private function _resolve ($lang) {
        $supported  = explode(',', $this->_conf->get('language_supported'));

        // try with 5 chars as en-GB
        $code       = substr($lang, 0, 5);

        if (in_array($code, $supported)) {
            return $code;
        }

        // try with 2 chars as en
        $code       = substr($lang, 0, 2);

        if (in_array($code, $supported)) {
            return $code;
        }

        // fallback to default better have something here
        return $this->_conf->get('language_default');
    }

}
