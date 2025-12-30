<?php
/**
 * 순수 PHP 템플릿 엔진
 * 별도의 템플릿 언어 없이 순수 PHP 사용
 */

class Template {
    private $layout = 'layout';
    private $data = [];

    /**
     * 뷰 렌더링
     */
    public function render($view, $data = []) {
        $this->data = $data;

        // 데이터를 변수로 추출
        extract($data);

        // 뷰 파일 경로
        $view_file = APP_PATH . '/views/' . $view . '.php';

        if (!file_exists($view_file)) {
            die("View not found: $view");
        }

        // 출력 버퍼링 시작
        ob_start();

        // 뷰 파일 포함
        require $view_file;

        // 컨텐츠 가져오기
        $content = ob_get_clean();

        // 레이아웃 사용
        if ($this->layout) {
            $this->render_layout($content, $data);
        } else {
            echo $content;
        }
    }

    /**
     * 레이아웃 렌더링
     */
    private function render_layout($content, $data) {
        extract($data);

        $layout_file = APP_PATH . '/views/layouts/' . $this->layout . '.php';

        if (!file_exists($layout_file)) {
            // 레이아웃이 없으면 컨텐츠만 출력
            echo $content;
            return;
        }

        require $layout_file;
    }

    /**
     * 레이아웃 설정
     */
    public function setLayout($layout) {
        $this->layout = $layout;
    }

    /**
     * 레이아웃 없이 렌더링
     */
    public function noLayout() {
        $this->layout = null;
    }

    /**
     * 부분 뷰 포함
     */
    public function partial($view, $data = []) {
        extract($data);

        $partial_file = APP_PATH . '/views/partials/' . $view . '.php';

        if (file_exists($partial_file)) {
            require $partial_file;
        }
    }
}

/**
 * 뷰 헬퍼 함수
 */
function view($view, $data = []) {
    $template = new Template();
    $template->render($view, $data);
}

/**
 * JSON 뷰 헬퍼
 */
function view_json($data, $status = 200) {
    json_response($data, $status);
}

/**
 * 부분 뷰 포함
 */
function include_partial($view, $data = []) {
    $template = new Template();
    $template->partial($view, $data);
}
