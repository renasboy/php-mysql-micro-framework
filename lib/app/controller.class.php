<?php
namespace app;

class controller {

    protected   $_input         = [];
    protected   $_data          = [];

    // these are the controller dependencies
    protected $_dependencies        = [];

    // these are the dependency objects library
    protected   $_api_client    = null;
    protected   $_request       = null;
    protected   $_session       = null;
    protected   $_validator     = null;
    protected   $_view          = null;
    protected   $_conf          = null;
    protected   $_logger        = null;
    protected   $_error         = null;

    public function __construct (
        request     $request,
        session     $session,
        validator   $validator,
        api_client  $api_client,
        view        $view,
        conf        $conf,
        logger      $logger,
        error       $error
    ) {
        $this->_request         = $request;
        $this->_session         = $session;
        $this->_validator       = $validator;
        $this->_api_client      = $api_client;
        $this->_view            = $view;
        $this->_conf            = $conf;
        $this->_logger          = $logger;
        $this->_error           = $error;
    }

    public function dispatch () {
        $this->_read_input();
        $this->_validate_input(); 
        $this->_execute();
        $this->_view->execute();
        $this->_view->render();
    }

    protected function _read_input () {
        // TODO read input as options in API
        // array_intersect_key($data, $default) + array_diff_key($default, $data)
        $this->_input = $this->_default_input;
        foreach ($this->_input as $name => $value) {
            if ($name == 'file') {
                $input = $this->_request->get($name, 'file');
            }
            else {
                $input = $this->_request->get($name);
                if (is_array($input)) {
                    array_map('urldecode', $input);
                }
                else {
                    $input = urldecode($input);
                }
            }
            if ($input) {
                $this->_input[$name] = $input;
            }
        }
    }

    // retrieve the controller dependency if no
    // value is specified otherwise sets the
    // new relation objects
    public function dependencies ($value = null) {
        if ($value === null) {
            return $this->_dependencies;
        }
        $this->_dependencies = $value;
    }
}
