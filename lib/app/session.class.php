<?php
namespace app;

class session {

    public function __construct ($name, $host, $lifetime) {
        // if session is already started
        if (session_id()) {
            return true;
        }
        ini_set('session.gc_maxlifetime', $lifetime);
        session_name($name);
        session_set_cookie_params($lifetime, '/', $host);
        // Added session_cache_limiter and the next header in order
        // for the clipper to work on IE, do not understand why, but
        // we also had it in the other systems, only on IE
        session_cache_limiter('must-revalidate');
        session_start();
        header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
    }

    public function set ($context, $key, $value) {
        $_SESSION[$context][$key] = $value;
    }

    public function get ($context, $key) {
        if (array_key_exists($context, $_SESSION)) {
            if (array_key_exists($key, $_SESSION[$context])) {
                return $_SESSION[$context][$key];
            }
        }
        return null;
    }

    public function del ($context, $key) {
        if (array_key_exists($context, $_SESSION)) {
            if (array_key_exists($key, $_SESSION[$context])) {
                unset($_SESSION[$context][$key]);
            }
        }
        return true;
    }
}
