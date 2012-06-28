<?php
namespace app;

class validator extends \core\validator {

    private $_api_client    = null;

    public function __construct (
        api_client          $api_client
    ) {
        $this->_api_client  = $api_client;
    }

    public function is_callback ($value) {
        return preg_match('/^jQuery[0-9_]+$/', $value) || preg_match('/^jsonp[0-9_]+$/', $value);
    }

    public function is_cms_flag ($value) {
        return in_array($value, ['active']);
    }

    public function is_cms_entity ($value) {
        return in_array($value, ['hello_world']);
    }

    public function is_html ($value) {
        return is_string($value) && strlen($value) !== 0 && strlen($value) != strlen(strip_tags($value));
    }

    public function is_xml ($value) {
        // TODO implement
        return isset($value);
    }

    // This is the CDN size
    public function is_img_size ($value) {
        return $this->is_number($value) || $value == 'auto';
    }

    // This is the CDN image
    public function is_img_file ($value) {
        return is_file($value) && strstr(mime_content_type($value), 'image/') && !strstr(mime_content_type($value), 'bmp');
    }

    // This is the CDN params
    public function is_img_params ($value) {
        $parts = explode('-', $value);
        if (count($parts) == 3) {
            if (in_array($parts[0], ['crop', 'fit', 'scale'])) {
                if (in_array($parts[2], ['black', 'white'])) {
                    $parts = explode('x', $parts[1]);
                    if (count($parts) == 2) {
                        if ($this->is_img_size($parts[0]) && $this->is_img_size($parts[1])) {
                            if ($parts[0] != 'auto' || $parts[1] != 'auto') {
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    // This is the size while uploding
    public function is_image_size ($value) {
        return $this->is_number($value) && $value >= 300;
    }

    // This is the image while uploading
    public function is_image ($value) {
        return is_array($value) && array_key_exists('type', $value) && strstr($value['type'], 'image/');
    }

    // This is the image while uploading
    public function is_import ($value) {
        return is_array($value) && array_key_exists('type', $value) && strstr($value['type'], 'text/');
    }

    public function is_entity ($entity, $field, $value, $options = []) {
        if (!$value) {
            return false;
        }
        $input = [
            $field          => $value,
            'active'        => [0, 1],
            'offset_start'  => 0,
            'offset_end'    => 1
        ] + $options;
        $entity = $this->_api_client->get('/' . $entity, $input);
        if (!$entity) {
            return false;
        }
        return true;
    }

    public function is_page ($value) {
        return $this->is_number($value);
    }

    public function is_autocomplete ($value) {
        return in_array($value, ['hello_world']);
    }

    public function is_context ($value) {
        return in_array($value, ['hello_world']);
    }

    public function is_key ($value) {
        return in_array($value, ['filter', 'form', 'error']);
    }

    public function is_filter ($value, $options) {
        $parts = explode(':', $value);
        if (count($parts) == 3) {
            if (in_array($parts[0], ['add', 'del'])) {
                if (in_array($parts[1], $options)) {
                    return true;
                }
            }
        }
        return false;
    }

    // return the seo value for string
    // this is not real validation
    public function seo ($value) {
        $transliteration                        = [
            '/ä|æ|ǽ/'                           => 'ae',
            '/ö|œ/'                             => 'oe',
            '/ü/'                               => 'ue',
            '/Ä/'                               => 'Ae',
            '/Ü/'                               => 'Ue',
            '/Ö/'                               => 'Oe',
            '/À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ/'           => 'A',
            '/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/'           => 'a',
            '/Ç|Ć|Ĉ|Ċ|Č/'                       => 'C',
            '/ç|ć|ĉ|ċ|č/'                       => 'c',
            '/Ð|Ď|Đ/'                           => 'D',
            '/ð|ď|đ/'                           => 'd',
            '/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/'               => 'E',
            '/è|é|ê|ë|ē|ĕ|ė|ę|ě/'               => 'e',
            '/Ĝ|Ğ|Ġ|Ģ/'                         => 'G',
            '/ĝ|ğ|ġ|ģ/'                         => 'g',
            '/Ĥ|Ħ/'                             => 'H',
            '/ĥ|ħ/'                             => 'h',
            '/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/'             => 'I',
            '/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/'             => 'i',
            '/Ĵ/'                               => 'J',
            '/ĵ/'                               => 'j',
            '/Ķ/'                               => 'K',
            '/ķ/'                               => 'k',
            '/Ĺ|Ļ|Ľ|Ŀ|Ł/'                       => 'L',
            '/ĺ|ļ|ľ|ŀ|ł/'                       => 'l',
            '/Ñ|Ń|Ņ|Ň/'                         => 'N',
            '/ñ|ń|ņ|ň|ŉ/'                       => 'n',
            '/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/'           => 'O',
            '/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º/'         => 'o',
            '/Ŕ|Ŗ|Ř/'                           => 'R',
            '/ŕ|ŗ|ř/'                           => 'r',
            '/Ś|Ŝ|Ş|Š/'                         => 'S',
            '/ś|ŝ|ş|š|ſ/'                       => 's',
            '/Ţ|Ť|Ŧ/'                           => 'T',
            '/ţ|ť|ŧ/'                           => 't',
            '/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/'   => 'U',
            '/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/'   => 'u',
            '/Ý|Ÿ|Ŷ/'                           => 'Y',
            '/ý|ÿ|ŷ/'                           => 'y',
            '/Ŵ/'                               => 'W',
            '/ŵ/'                               => 'w',
            '/Ź|Ż|Ž/'                           => 'Z',
            '/ź|ż|ž/'                           => 'z',
            '/Æ|Ǽ/'                             => 'AE',
            '/ß/'                               => 'ss',
            '/Ĳ/'                               => 'IJ',
            '/ĳ/'                               => 'ij',
            '/Œ/'                               => 'OE',
            '/ƒ/'                               => 'f'
        ];

        $dash           = '-';
        $quoted_dash    = preg_quote($dash, '/');

        // Spaces
        $replacement    = [
            '/[^\s\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',
            '/\\s+/'                                        => $dash,
            sprintf('/^[%s]+|[%s]+$/', $quoted_dash, $quoted_dash) => ''
        ];

        $replace        = $transliteration + $replacement;

        $seo            = preg_replace(array_keys($replace), array_values($replace), $value);

        // if longer than 70 cut in the last dash "-" sign
        // since database field is 80 chars long
        if (strlen($seo) > 70) {
            $seo        = substr($seo, 0, strrpos(substr($seo, 0, 70), '-'));
        }
        return $seo;
    }
}
