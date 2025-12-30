<?php

namespace Core\Routing;

class Router
{
    private $routes = [];
    private $groupPrefix = '';
    private $groupMiddleware = [];

    public function get($path, $handler, $middleware = [])
    {
        $this->addRoute('GET', $path, $handler, $middleware);
        return $this;
    }

    public function post($path, $handler, $middleware = [])
    {
        $this->addRoute('POST', $path, $handler, $middleware);
        return $this;
    }

    public function put($path, $handler, $middleware = [])
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
        return $this;
    }

    public function delete($path, $handler, $middleware = [])
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
        return $this;
    }

    public function group($prefix, $callback, $middleware = [])
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->groupPrefix = $previousPrefix . $prefix;
        $this->groupMiddleware = array_merge($previousMiddleware, $middleware);

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;

        return $this;
    }

    private function addRoute($method, $path, $handler, $middleware = [])
    {
        $path = $this->groupPrefix . $path;
        $middleware = array_merge($this->groupMiddleware, $middleware);

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
            'pattern' => $this->convertToPattern($path)
        ];
    }

    private function convertToPattern($path)
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch($method, $uri)
    {
        $uri = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                foreach ($route['middleware'] as $middleware) {
                    $middlewareInstance = new $middleware();
                    if (!$middlewareInstance->handle()) {
                        return;
                    }
                }

                return $this->callHandler($route['handler'], $params);
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }

    private function callHandler($handler, $params = [])
    {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }

        if (is_string($handler)) {
            list($controller, $method) = explode('@', $handler);
            $controller = "App\\Controllers\\{$controller}";

            if (class_exists($controller)) {
                $controllerInstance = new $controller();
                if (method_exists($controllerInstance, $method)) {
                    return call_user_func_array([$controllerInstance, $method], array_values($params));
                }
            }
        }

        throw new \Exception("Handler not found");
    }

    public function getRoutes()
    {
        return $this->routes;
    }
}
