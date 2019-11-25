<?php

namespace Testify\Router;

use Testify\Component\I18n;
use Testify\Component\Arrays;

class Router {

    private static $_instance = null;

    private $request;
    private $supportedHttpMethods = array(
        "GET",
        "POST",
        "PUT",
        "DELETE"
    );

    private function __construct(IRequest $request) {
        $this->request = $request;

        $this->get('/404', function($request) {
            return new Response(
                "404: Page not found!",
                404
            );
        });
    }

    protected function __clone() { }

    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    function __call($name, $args) {
        list($route, $method) = $args;
        if(!in_array(strtoupper($name), $this->supportedHttpMethods)) {
            $this->invalidMethodHandler();
        }
        $this->{strtolower($name)}[$this->formatRoute($route)] = $method;
    }

    private function formatRoute($route) {
        $result = rtrim($route, '/');
        if ($result === '')
            return '/';
        return $result;
    }

    private function invalidMethodHandler() {
        header("{$this->request->serverProtocol} 405 Method Not Allowed");
    }

    private function defaultRequestHandler() {
        header('Location: /404');
    }

    private function resolve() {
        $methodsList = array();
        if (property_exists($this, strtolower($this->request->requestMethod)))
            $methodsList = $this->{strtolower($this->request->requestMethod)};
        $formatedRoute = parse_url($this->request->requestUri, PHP_URL_PATH);
        $formatedRoute = $this->formatRoute($formatedRoute);
        $formatedRoute = I18n::getInstance()->setLangFromURL($formatedRoute);

        foreach ($methodsList as $route => $method) {

            $route_cmp = preg_replace('/\<[a-z_]+:int\>/i', '([0-9]+)', $route);
            $route_cmp = preg_replace('/\<[a-z_]+:str\>/i', '(.+)', $route_cmp);
            $route_cmp = str_replace('/', '\/', $route_cmp);
            $route_cmp = str_replace('?', '\?', $route_cmp);

            $values = array();
            if (preg_match('/^' . $route_cmp . '$/i', $formatedRoute, $matches)) {
                array_shift($matches);
                array_push($values, $this->request);
                foreach ($matches as $value) {
                    array_push($values, $value);
                }
                $response = call_user_func_array($method, $values);
                if (!($response instanceof \Testify\Router\Response))
                    throw new \Exception("The controller response need to be a Response object!");

                http_response_code($response->getHttpCode());
                echo $response->getContent();
                return;
            }
        }
        $this->defaultRequestHandler();
    }

    public static function getInstance(IRequest $request=NULL): Router {

        if(is_null(self::$_instance)) {
            self::$_instance = new Router($request);
        }

        return self::$_instance;
    }

    function __destruct() {
        $this->resolve();
    }
}
