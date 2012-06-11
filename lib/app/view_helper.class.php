<?php
namespace app;

class view_helper {

    private $_conf          = null;
    private $_metas         = [];

    public function __construct (
        conf                $conf
    ) {
        $this->_conf        = $conf;
    }

    // TODO really pack the files
    public function pack_js ($all_js) {
        // generate cache key
        $version = $this->_conf->get('__version');
        asort($all_js);
        $key = md5($version . implode($all_js));
        ksort($all_js);

        $file = sprintf('%s/js/%s.js', $this->_conf->get('cache_root'), $key);
        if (!is_file($file)) {

            $dir = sprintf('%s/js', $this->_conf->get('cache_root'));
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            foreach ($all_js as $js) {
                $js_file = sprintf('%s%s', $this->_conf->get('pub_root'), $js);
                file_put_contents($file, file_get_contents($js_file), FILE_APPEND);
            }
        }
        return sprintf('/cache/js/%s.js', $key);
    }

    // TODO really pack the files
    public function pack_css ($all_css) {
        // generate cache key
        $version = $this->_conf->get('__version');
        asort($all_css);
        $key = md5($version . implode($all_css));
        ksort($all_css);

        $file = sprintf('%s/css/%s.css', $this->_conf->get('cache_root'), $key);
        if (!is_file($file)) {

            $dir = sprintf('%s/css', $this->_conf->get('cache_root'));
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            foreach ($all_css as $css) {
                $css_file = sprintf('%s%s', $this->_conf->get('pub_root'), $css);
                file_put_contents($file, file_get_contents($css_file), FILE_APPEND);
            }
        }
        return sprintf('/cache/css/%s.css', $key);
    }

    public function set_metas ($metas) {
        // only the first get set
        if ($this->_metas == []) {
            $this->_metas = $metas;
        }
    }

    public function add_metas ($data) {
        $search     = [
            'HTML_TITLE',
            'HTML_META_DESCRIPTION',
            'HTML_META_KEYWORDS'
        ];
        
        // these are the default values
        if (!array_key_exists('title', $this->_metas)) {
            $this->_metas['title']          = 'This is the default html title';
        }
        if (!array_key_exists('description', $this->_metas)) {
            $this->_metas['description']    = 'This is the default html meta description.';
        }
        if (!array_key_exists('keywords', $this->_metas)) {
            $this->_metas['keywords']       = 'This is the default html meta keywords.';
        }

        $replace    = [
            $this->_metas['title'],
            $this->_metas['description'],
            $this->_metas['keywords']
        ];

        $data = str_replace($search, $replace, $data);

        return $data;
    }

    public function encode ($value) {
        return urlencode($value);
    }

    public function html ($value) {
        return htmlentities($value);
    }

    public function css ($file) {
        return sprintf('<link rel="stylesheet" href="%s">', $file);
    }

    public function js ($file) {
        return sprintf('<script src="%s"></script>', $file);
    }

    public function title ($label) {
        return sprintf('<h1>%s</h1>', $label);
    }

    public function subtitle ($label) {
        return sprintf('<h2>%s</h2>', $label);
    }

    public function description ($text) {
        return sprintf('<p>%s</p>', $text);
    }

    public function small ($text) {
        return sprintf('<small>%s</small>', $text);
    }
}
