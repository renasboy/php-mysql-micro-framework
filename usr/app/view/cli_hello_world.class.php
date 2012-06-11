<?php
namespace app\view;

class cli_hello_world extends \app\view {

    protected $_top     = 'none';

    protected $_view    = 'cli';

    protected $_subs    = [];

    protected $_css     = [];

    protected $_js      = [];

    public function execute () {
        print 'Hello world' . chr(10);
    }
}
