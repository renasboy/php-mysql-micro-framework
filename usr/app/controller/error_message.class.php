<?php
namespace app\controller;

class error_message extends \app\simple_controller {

    // default input values in 
    // case no input is given
    protected $_default_input  = [
        'code'   => null
    ];

    protected function _validate_input () {
        // validate the input param
        $this->_validator->validate('is_error_code',    $this->_input['code']);

        if ($this->_validator->error()) {
            $this->_error->bad_request('error detail validation failed:' . $this->_validator->error());
        }
    }

    protected function _execute () {
        // map input param to commands
        $this->_view->set('code', $this->_input['code']);
    }
}
