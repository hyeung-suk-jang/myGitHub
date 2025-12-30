<?php
/**
 * 순수 PHP 라우터
 * 복잡한 정규식 없이 단순한 패턴 매칭 사용
 */

class Router {
    private $routes = [];
    private $current_route = null;

    /**
     * GET 라우트 등록
     */
    public function get($pattern, $controller, $action) {
        $this->add_route('GET', $pattern, $controller, $action);
    }

    /**
     * POST 라우트 등록
     */
    public function post($pattern, $controller, $action) {
        $this->add_route('POST', $pattern, $controller, $action);
    }

    /**
     * 라우트 추가
     */
    private function add_route($method, $pattern, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * 라우트 실행
     */
    public function dispatch() {
        $uri = $this->get_uri();
        $method = request_method();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->match_pattern($route['pattern'], $uri);

            if ($params !== false) {
                $this->current_route = $route;
                return $this->call_controller($route['controller'], $route['action'], $params);
            }
        }

        // 404 처리
        $this->not_found();
    }

    /**
     * URI 가져오기
     */
    private function get_uri() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // index.php 제거
        $uri = str_replace('/index.php', '', $uri);

        // 마지막 슬래시 제거
        $uri = rtrim($uri, '/');

        // 빈 URI는 홈으로
        return $uri ?: '/';
    }

    /**
     * 패턴 매칭
     */
    private function match_pattern($pattern, $uri) {
        // 정확한 매칭
        if ($pattern === $uri) {
            return [];
        }

        // 파라미터 매칭 (예: /user/:id => /user/123)
        $pattern_parts = explode('/', trim($pattern, '/'));
        $uri_parts = explode('/', trim($uri, '/'));

        if (count($pattern_parts) !== count($uri_parts)) {
            return false;
        }

        $params = [];

        for ($i = 0; $i < count($pattern_parts); $i++) {
            $pattern_part = $pattern_parts[$i];
            $uri_part = $uri_parts[$i];

            // 파라미터 (: 시작)
            if (strpos($pattern_part, ':') === 0) {
                $param_name = substr($pattern_part, 1);
                $params[$param_name] = $uri_part;
            }
            // 정확히 일치해야 함
            else if ($pattern_part !== $uri_part) {
                return false;
            }
        }

        return $params;
    }

    /**
     * 컨트롤러 호출
     */
    private function call_controller($controller_name, $action, $params) {
        $controller_file = APP_PATH . '/controllers/' . $controller_name . '.php';

        if (!file_exists($controller_file)) {
            die("Controller not found: $controller_name");
        }

        require_once $controller_file;

        $controller_class = $controller_name . 'Controller';

        if (!class_exists($controller_class)) {
            die("Controller class not found: $controller_class");
        }

        $controller = new $controller_class();

        if (!method_exists($controller, $action)) {
            die("Action not found: $action in $controller_class");
        }

        // 파라미터를 배열로 전달
        return call_user_func_array([$controller, $action], $params);
    }

    /**
     * 404 처리
     */
    private function not_found() {
        http_response_code(404);
        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>404 - Page Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { font-size: 50px; margin: 0; color: #333; }
        p { font-size: 20px; color: #666; }
        a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <h1>404</h1>
    <p>페이지를 찾을 수 없습니다.</p>
    <a href="/">홈으로 돌아가기</a>
</body>
</html>';
        exit;
    }
}
