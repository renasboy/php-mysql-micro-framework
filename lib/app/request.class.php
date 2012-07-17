<?php
namespace app;

class request extends \core\request {

    private $_controller        = null;
    private $_uri               = null;

    public function language () {
        if (!array_key_exists('HTTP_ACCEPT_LANGUAGE', $this->_server)) {
            // default to en-GB
            $this->_server['HTTP_ACCEPT_LANGUAGE']  = 'en-GB';
        }
        return $this->_server['HTTP_ACCEPT_LANGUAGE'];
    }

    public function redirect ($url) {
        if ($url == 'referer') {
            $url = $this->referer();
            if (!$url) {
                $url = '/';
            }
        }
        header('Location: ' . $url);
        exit;
    }

    public function referer () {
        if (!array_key_exists('HTTP_REFERER', $this->_server)) {
            return false;
        }
        return $this->_server['HTTP_REFERER'];
    }

    public function controller () {
        return $this->_controller;
    }

    private function _requests ($section = null) {
        return $this->_conf->get($section);
    }

    // retrieve controller from configuration
    // file request.ini. it loops through the
    // section variables generate a regular
    // expression out of the value and try
    // to match it with the current request uri
    private function _identify_request () {
        $uri = $this->uri();
        if (!$uri) {
            $this->_controller = $this->_conf->get('default.controller');
            return true;
        }
        $requests = $this->_requests();
        foreach ($requests as $section => $_requests) {
            foreach ($_requests as $controller => $request) {
                $regex = substr($request, 1);
                $regex = preg_replace('/{[^\}]+}/', '[^/]+', $regex);
                $regex = str_replace('/', '\/', $regex);
                if (preg_match('/^' . $regex . '\/?$/', $uri)) {
                    $this->_controller  = $section . '_' . $controller;
                    $this->_uri         = substr($request, 1);
                    return true;
                }
            }
        }
        $this->_error->not_found('Controller not found ' . $this->uri());
    }

    protected function _append_params () {
        $this->_identify_request();
        $this->_append_uri_params();
    }

    // add parameters from uri to the _REQUEST using
    // the resource mapping in the configuration file
    private function _append_uri_params () {
        $request_uri    = explode('/', $this->uri());
        $resource_uri   = explode('/', $this->_uri);
        $request        = array_combine($resource_uri, array_pad($request_uri, count($resource_uri), null));
        foreach ($request as $key => $val) {
            if ($key === $val) {
                continue;
            }
            // TODO check if enough to remove the first { and last }
            $this->_request[substr($key, 1, -1)] = $val;
        }
    }
}
