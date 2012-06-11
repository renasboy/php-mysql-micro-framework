<?php
namespace app;

class memcache {

    private $_memcached     = null;
    private $_port          = null;
    private $_enabled       = false;

    // TODO pass also host here from conf
    public function __construct ($port) {
        $this->_port        = $port;
    }

    private function _init () {
        if ($this->_memcached == null) {
            $this->_memcached = new \memcached();
            $this->_memcached->addserver('localhost', $this->_port);
        }
    }

    public function key ($data) {
        return serialize($data);
    }

    public function set ($key, $value) {
        $this->_init();
        return $this->_memcached->set($key, $value);
    }

    public function get ($key) {
        $this->_init();
        return $this->_memcached->get($key);
    }

    public function flush () {
        $this->_init();
        return $this->_memcached->flush();
    }
}
