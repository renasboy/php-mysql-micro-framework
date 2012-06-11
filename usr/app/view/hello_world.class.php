<?php
namespace app\view;

class hello_world extends \app\view {

    protected $_view = 'hello_world';

    protected $_subs = [];

    protected $_css = [];

    protected $_js = [];

    public function execute () {

        $hello_world = $this->_api_client->get('/hello_world', []);

        $this->set('hello_world', $hello_world);
    }
}
