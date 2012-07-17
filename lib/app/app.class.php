<?php
namespace app;

class app {

    private $_controller    = null;
    private $_view          = null;
    private $_view_helper   = null;
    private $_memcache      = null;

    private $_request       = null;
    private $_validator     = null;
    private $_session       = null;
    private $_error         = null;
    private $_logger        = null;
    private $_conf          = null;

    // constructor will attempt to execute
    // the remote request, if that fails
    // it will proceed with internal error calls
    public function __construct ($_request = null, $_server = null) {
        // This is mainly for CLI calls
        if ($_request === null && $_server === null) {
            $_request   = $_REQUEST;
            $_server    = $_SERVER;
        }

        try {
            $this->_dispatch($_request, $_server);
        }
        catch (\Exception $exception) {
            // TODO check if the message is good as is, since it can expose some internals
            header('HTTP/1.0 ' . $exception->getCode() . ' ' . str_replace(chr(10), "", $exception->getMessage()));
            try {
                // there was an error, clean buffer and compose error
                ob_end_clean();

                $_request   = [];
                $_server    = [
                    'REQUEST_METHOD'    => 'get',
                    'REQUEST_URI'       => '/error/' . $exception->getCode()
                ];

                $this->_dispatch($_request, $_server);
            }
            catch (\Exception $exception) {
                print $this->_error->output($exception->getCode());
            }
        }
    }

    // this is the main method that makes sure
    // everything is initialized and the dependency
    // is properly injected
    private function _dispatch ($_request, $_server) {
        $this->_conf            = new \app\conf(APP_CONF);

        if ($this->_conf->get('__maintenance')) { 
            die(file_get_contents($this->_conf->get('template_root') . '/maintenance.tpl.php'));
        }

        // Move the instatiation of the view_helper here
        // to the top so we can start buffering in case of errors
        $this->_view_helper     = new \app\view_helper($this->_conf);

        // start buffering in case any errors occur
        ob_start(array($this->_view_helper, 'add_metas'));

        $this->_logger          = new \app\logger(
            $this->_conf->get('logger_root'),
            $this->_conf->get('logger_level')
        );
        $this->_logger->debug(sprintf(
            'Started logger at %s and level %s' ,
            $this->_conf->get('logger_root'),
            $this->_conf->get('logger_level')));

        $this->_error           = new \app\error(
            $this->_conf->get('error_reporting'),
            $this->_logger
        );

        $this->_session         = new \app\session (
            $this->_conf->get('__name'),
            $this->_conf->get('base_host'),
            $this->_conf->get('session_lifetime')
        );

        $this->_request         = new \app\request(
            $_request,
            $_server,
            new conf($this->_conf->get('conf_request'), true),
            $this->_error
        );

        $this->_api_client      = new \app\api_client($this->_conf->get('api_root'));

        $this->_validator       = new \app\validator($this->_api_client);

        $controller             = $this->_request->controller();
        $view                   = '\app\view\\' . $controller;
        $controller             = '\app\controller\\' . $controller;

        $this->_logger->debug(sprintf(
            'Controller %s and View %s' ,
            $controller,
            $view));

        $this->_memcache        = new \app\memcache($this->_conf->get('memcache_port'));

        $language               = $this->_request->language();
        $this->_logger->debug(sprintf(
            'Language %s' ,
            $language));

        $this->_language        = new \app\language($language, $this->_conf);

        $this->_view            = new $view(
            $this->_view_helper,
            $this->_memcache,
            $this->_api_client,
            $this->_language,
            $this->_logger,
            $this->_error,
            $this->_conf
        );

        // instantiate all subviews for top_view
        // and inject them in the view_parent
        $this->_populate_subs();

        // find out which top view to use
        // initiate top view and assign
        // the current view as main view
        $top                    = $this->_request->get('top');
        if ($top === null || !in_array($top, $this->_view->tops())) {
            $top                = $this->_view->top();
        }
        $top                    = '\app\view\\top_' . $top;
        $this->_top             = new $top(
            $this->_view_helper,
            $this->_memcache,
            $this->_api_client,
            $this->_language,
            $this->_logger,
            $this->_error,
            $this->_conf
        );
        $this->_populate_subs($this->_top);
        $subs = $this->_top->subs();
        $subs['main']           = $this->_view;
        $this->_top->subs($subs);

        $this->_controller      = new $controller(
            $this->_request,
            $this->_session,
            $this->_validator,
            $this->_api_client,
            $this->_top,
            $this->_conf,
            $this->_logger,
            $this->_error
        );

        // inject library dependencies
        // defined in the controllers
        $this->_inject_dependencies();

        $this->_controller->dispatch();

        // we are safe print buffer
        ob_end_flush();
    }

    private function _populate_subs ($view = null) {
        if ($view === null) {
            $view = $this->_view;
        }
        $subs = $view->subs();
        foreach ($subs as $name => $object) {
            if ($object === null) {
                $sub_view = '\app\view\\' . $name;
                $subs[$name] = new $sub_view(
                    $this->_view_helper,
                    $this->_memcache,
                    $this->_api_client,
                    $this->_language,
                    $this->_logger,
                    $this->_error,
                    $this->_conf
                );
                $this->_populate_subs($subs[$name]);
            }
        }
        $view->subs($subs);
    }

    private function _inject_dependencies () {
        $dependencies = $this->_controller->dependencies();
        foreach ($dependencies as $name => $object) {
            if ($object === null) {
                $object = '\app\\' . $name;
                $dependencies[$name] = new $object();
            }
        }
        $this->_controller->dependencies($dependencies);
    }
}
