<?php
namespace app;

class view {

    private $_data          = [];

    protected $_conf        = null;
    protected $_error       = null;
    protected $_logger      = null;
    protected $_api_client  = null;
    protected $_helper      = null;
    // TODO, added public cache to be called from controller, fix this
    public    $_cache       = null;

    protected $_view        = null;
    protected $_top         = 'full';
    protected $_tops        = ['full', 'empty', 'none'];
    protected $_subs        = [];
    protected $_js          = [];
    protected $_css         = [];

    public function __construct (
        view_helper         $helper,
        memcache            $memcache,
        api_client          $api_client,
        logger              $logger,
        error               $error,
        conf                $conf
    ) {
        $this->_helper      = $helper;
        $this->_cache       = $memcache;
        $this->_api_client  = $api_client;
        $this->_logger      = $logger;
        $this->_error       = $error;
        $this->_conf        = $conf;
    }

    public function execute () {
        return true;
    }

    // retrieve items from the data
    // if key is provided otherwise
    // return the whole data
    public function get ($key = null) {
        if ($key === null) {
            return $this->_data;
        }
        if (array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        }
        return null;
    }

    // set items in the data
    // and call set subs to set
    // the same value for all sub views
    public function set ($key, $value) {
        $this->_data[$key] = $value;
        $this->_set_subs($key, $value);
    }

    // recursive sets the value to all subs
    private function _set_subs ($key, $value) {
        foreach ($this->_subs as $sub) {
            $sub->set($key, $value);
        }
    }

    // add subview inside template
    public function add ($view) {
        $view = $this->_subs[$view];
        $view->execute();
        $view->render();
    }

    // renders the view and make
    // the data items available
    // inside the view
    public function render ($template = null) {
        if ($template === null) {
            $template = $this->_view;
        }
        $view_template = $this->_conf->get('template_root') . '/' . $template . '.tpl.php';
        if (is_file($view_template)) {
            // make vars avaialble in the template
            // can overwrite view_template
            foreach ($this->get() as $key => $val) {
                $$key = $val;
            }
            // make helper, conf and view itself available
            $helper = $this->_helper;
            $view   = $this;
            $conf   = $this->_conf;
            include $view_template;
        }
    }

    public function mail ($template) {
        ob_start();
        $this->render($template);
        return ob_get_clean();
    }

    // return the default top if none or
    // invalid top is provided
    public function top () {
        return $this->_top;
    }

    // return the available tops
    public function tops () {
        return $this->_tops;
    }

    // retrieve the subviews if no
    // value is specified otherwise
    // sets the new subviews objects
    public function subs ($value = null) {
        if ($value === null) {
            return $this->_subs;
        }
        $this->_subs = $value;
    }

    public function css () {
        return array_unique(array_merge($this->_css, $this->_subs_css()));
    }

    protected function _subs_css ($view = null) {
        if ($view === null) {
            $view = $this;
        }
        $css = [];
        $subs = $view->subs();
        foreach ($subs as $sub) {
            $css = array_merge($css, $sub->css(), $this->_subs_css($sub));
        }
        return $css;
    }

    public function js () {
        return array_unique(array_merge($this->_js, $this->_subs_js()));
    }

    protected function _subs_js ($view = null) {
        if ($view === null) {
            $view = $this;
        }
        $js = [];
        $subs = $view->subs();
        foreach ($subs as $sub) {
            $js = array_merge($js, $sub->js(), $this->_subs_js($sub));
        }
        return $js;
    }
}
