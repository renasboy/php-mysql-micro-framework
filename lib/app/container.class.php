<?php
/*
TODO use the container to build the app.
This is an idea of using the DIContainer to build the app.
It worked once with few dependencies, should still work now.
*/
namespace app;

class app {

    protected $dependencies   = [];

    public function __construct () {

        $this->app_conf = APP_CONF;

        $this->conf = $this->shared(function ($app) {
            return new \app\conf($app->app_conf);
        });

        $this->logger = $this->shared(function ($app) {
            return new logger(
                $app->conf->get('logger_root'),
                $app->conf->get('logger_level')
            );
        });

        $this->error = $this->shared(function ($app) {
            return new \app\error(
                $app->conf->get('error_reporting'),
                $app->logger
            );
        });

        $this->request_conf = $this->shared(function ($app) {
            return new \app\conf($app->conf->get('conf_request'), true);
        });

        $this->request = $this->shared(function ($app) {
            return new \app\request($app->request_conf);
        });

        $this->router = $this->shared(function ($app) {
            return new \app\router (
                $app->request,
                $app->logger,
                $app->error
            );
        });

        $this->route = $this->router->route();

        $this->view = function ($app) {
            $view = '\app\view\\' . $app-router;
            return new $view (
                $app->conf
            );
        };

        $this->api_client = function ($app) {
            return new \app\api_client();
        };

        $this->controller = function ($app) {
            $controller = '\app\controller\\' . $app->route;
            return new $controller (
                $app->api_client,
                $app->view,
                $app->request,
                $app->error,
                $app->logger,
                $app->conf
            );
        };
    }

    public function __set ($key, $value) {
        $this->dependencies[$key] = $value;
    }

    public function __get ($key) {
        if (!array_key_exists($key, $this->dependencies)) {
            // error
        }

        if (is_callable($this->dependencies[$key])) {
            return $this->dependencies[$key]($this);
        }
        return $this->dependencies[$key];
    }

    public function shared ($callable) {
        return function ($app) use ($callable) {
            static $object;
            if ($object === null) {
                $object = $callable($app);
            }
            return $object;
        };
    }
}
