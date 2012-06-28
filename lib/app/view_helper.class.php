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

    public function button ($label, $class = '') {
        return sprintf('<button class="%s">%s</button>', $class, $label);
    }

    public function image ($src, $alt, $width, $height, $mode = 'fit', $color = 'white') {
        if (strpos($src, '/images/') !== 0 && strpos($src, 'http://') !== 0) {
            $cdn    = $this->_conf->get('cdn' . rand(1, 4) . '_host');
            $params = sprintf('%s-%sx%s-%s', $mode, $width, ($width == $height) ? 'auto' : $height, $color);
            $src    = str_replace('/', '/' . $params . '/', $src);
            $src    = sprintf('http://%s/%s', $cdn, $src); 
        }
        return sprintf('<img src="%s" alt="%s" width="%d" height="%d">', $src, $alt, $width, $height);
    }

    public function form ($action, $fields, $buttons = []) {
        $form       = sprintf('<form action="%s" method="post" enctype="multipart/form-data">', $action);
        $form       .= '<fieldset>';
        foreach ($fields as $field) {
            $form   .= $field;
        }
        $form       .= '</fieldset>';

        if ($buttons) {
            $form       .= '<div class="buttons">';
            foreach ($buttons as $button) {
                $form   .= $button;
            }
            $form       .= '</div>';
        }

        $form       .= '</form>';
        return $form;
    }

    public function form_input ($type, $id, $name, $placeholder) {
        return sprintf('<input type="%s" id="%s" name="%s" placeholder="%s" value="">', $type, $id, $name, $placeholder);
    }

    public function form_textarea ($id, $name, $placeholder) {
        return sprintf('<textarea id="%s" name="%s" placeholder="%s"></textarea>', $id, $name, $placeholder);
    }

    public function form_select ($id, $name, $placeholder, $options) {
        $form_select      = sprintf('<select id="%s" name="%s">', $id, $name);
        $form_select      .= sprintf('<option value="">%s</option>', $placeholder);
        foreach ($options as $_value => $_name) {
            $form_select  .= sprintf('<option value="%s">%s</option>', $_value, $_name);
        }
        $form_select      .= '</select>';
        return $form_select;
    }

    public function form_radio ($id, $name, $options) {
        $form_radio     = null;
        foreach ($options as $_value => $_name) {
            $radio_id   = $id . '-' . str_replace(' ', '-', $_name);
            $form_radio  .= '<div class="radio-line">';
            $form_radio  .= sprintf('<input type="radio" id="%s" name="%s" value="%s">', $radio_id, $name, $_value);
            $form_radio  .= sprintf('<label for="%s">%s</label>', $radio_id, $_name);
            $form_radio  .= '</div>';
        }
        return $form_radio;
    }

    public function form_checkbox ($id, $name, $label, $options) {
        $form_checkbox      = sprintf('<input type="checkbox" id="%s" name="%s" value="%s">', $id, $name, $options['value']);
        $form_checkbox      .= sprintf('<label for="%s">%s</label>', $id, $label);
        return $form_checkbox;
    }

    public function form_image ($id) {
        $form_image     = $this->image('/images/unknown.png', 'No image', 80, 80);
        $form_image     .= sprintf('<a href="#" id="%s">change image</a>', $id);
        $form_image     .= sprintf('<input type="file" name="file" id="file-%s">', $id);
        $form_image     .= sprintf('<frame name="upload-%s" id="upload-%s" style="width:0px;height:0px;"></iframe>', $id, $id);
        return $form_image;
    }

    public function form_line ($type, $name, $label = null, $placeholder = null, $info = null, $options = []) {
        $id                     = str_replace('_', '-', $name);

        $form_line  = '<div>';

        if (in_array($type, ['checkbox'])) {
            if (array_key_exists('label', $options)) {
                $form_line      .= sprintf('<label for="%s">%s</label>', $id, $options['label']);
            }
        }
        else if ($label) {
            $form_line          .= sprintf('<label for="%s">%s</label>', $id, $label);
        }

        switch ($type) {
            case 'text':
            case 'email':
            case 'password':
            case 'date':
            case 'hidden':
                $form_line      .= $this->form_input($type, $id, $name, $placeholder);
            break;

            case 'checkbox':
                $form_line      .= $this->form_checkbox($id, $name, $label, $options);
            break;

            case 'radio':
                $form_line      .= $this->form_radio($id, $name, $options);
            break;

            case 'select':
                $form_line      .= $this->form_select($id, $name, $placeholder, $options);
            break;

            case 'textarea':
                $form_line      .= $this->form_textarea($id, $name, $placeholder);
            break;

            case 'image':
                $form_line      .= $this->form_image($id, $label);
            break;
        }

        $form_line  .= '<div>';
        $form_line  .= '</div>';

        if ($info) {
            $form_line          .= sprintf('<span>%s</span>', $info);
        }

        $form_line  .= '</div>';
        return $form_line;
    }
}
