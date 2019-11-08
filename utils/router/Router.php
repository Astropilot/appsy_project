<?php

class Router {

    private static $_instance = null;

    private $request;
    private $supportedHttpMethods = array(
        "GET",
        "POST",
        "PUT",
        "DELETE"
    );
    private $noRouteHandler = null;

    private function __construct(IRequest $request) {
        $this->request = $request;
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
        if (!property_exists($this, strtolower($name)))
            $this->{strtolower($name)} = array(array("route" => $this->formatRoute($route), "method" => $method));
        else
            array_push(
                $this->{strtolower($name)},
                array("route" => $this->formatRoute($route), "method" => $method)
            );
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
        header("{$this->request->serverProtocol} 404 Not Found");

        if ($this->noRouteHandler !== null)
            echo call_user_func_array($this->noRouteHandler, array($this->request));
    }

    private function resolve() {
        $methodsList = array();
        if (property_exists($this, strtolower($this->request->requestMethod)))
            $methodsList = $this->{strtolower($this->request->requestMethod)};
        $formatedRoute = $this->formatRoute($this->request->requestUri);

        foreach ($methodsList as $method) {
            $route = $method['route'];

            $route_cmp = preg_replace('/<[a-z_]+>/i', '([0-9]+)', $route);
            $route_cmp = str_replace('/', '\/', $route_cmp);

            $values = array();
            if (preg_match('/^' . $route_cmp . '$/i', $formatedRoute, $matches)) {
                array_shift($matches);
                array_push($values, $this->request);
                foreach ($matches as $value) {
                    array_push($values, $value);
                }
                echo call_user_func_array($method['method'], $values);
                return;
            }
        }
        $this->defaultRequestHandler();
    }

    public function setNoRouteHandler($handler) {
        $this->noRouteHandler = $handler;
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
