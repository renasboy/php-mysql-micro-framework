<?php
namespace app;

class error extends \core\error {
    public function output ($code) {
        print $code;
        exit;
    }
}
