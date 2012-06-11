<?php
namespace app\controller;

class cli_hello_world extends \app\simple_controller {

    // default input values in 
    // case no input is given
    protected $_default_input  = [];

    protected function _validate_input () {
        if (PHP_SAPI != 'cli') {
            $this->_error->unauthorized('Cannot access CLI hello world using HTTP');
        }
    }

    protected function _execute () {}
}
