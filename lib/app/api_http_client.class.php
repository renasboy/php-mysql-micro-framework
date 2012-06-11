<?php
namespace app;

class api_http_client {

    private $_error     = [];

    public function error () {
        return $this->_error;
    }

    public function get ($resource, $options) {
        return $this->call('get', $resource, $options);
    }

    public function save ($resource, $options) {
        return $this->call('post', $resource, $options);
    }

    public function delete ($resource) {
        return $this->call('delete', $resource, $options);
    }

    public function call ($method, $resource, $options) {
        $content    = http_build_query($options);
        $length     = strlen($content);
        $context    = [
            'http'  => [
                'method' => $method,
                'header' => 'Connection:close; Content-Length: ' . $length,
                'content' => $content
            ]
        ];
        // TODO get api.localhost from conf
        return json_decode(file_get_contents('http://api.localhost' . $resource, false, stream_context_create($context)));
    }
}
?>
