<?php
namespace app;

class api_client {

    private $_error     = [];

    public function __construct ($api_root) {
        // already defined, this might be an exception catch
        if (defined('API_ROOT')) {
            return;
        }
        // define where the api is located
        define('API_ROOT', $api_root);
        define('API_CONF', API_ROOT . '/etc/api.ini');
    }

    public function error () {
        return $this->_error;
    }

    public function get ($resource, $options) {
        return $this->_call('get', $resource, $options);
    }

    public function save ($resource, $options) {
        return $this->_call('post', $resource, $options);
    }

    public function delete ($resource, $options) {
        return $this->_call('delete', $resource, $options);
    }

    private function _call ($method, $resource, $_request) {
        $_server                = [
            'REQUEST_METHOD'    => $method,
            'REQUEST_URI'       => $resource
        ];

        $api                    = new \api\api();
        try {
            $data               = $api->dispatch($_request, $_server);
        }
        catch (\Exception $exception) {
            $data               = false;
            $this->_error       = [
                'code'          => $exception->getCode(),
                'message'       => $exception->getMessage()
            ];
        }
        // TODO check the performance impact of this.
        // This seems to convert the arrays to objects
        return json_decode(json_encode($data));
    }

}
