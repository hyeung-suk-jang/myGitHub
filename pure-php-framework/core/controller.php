<?php
/**
 * 기본 컨트롤러 클래스
 * 모든 컨트롤러가 상속받는 베이스 클래스
 */

class Controller {
    protected $db;
    protected $template;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->template = new Template();
    }

    /**
     * 뷰 렌더링
     */
    protected function view($view, $data = []) {
        $this->template->render($view, $data);
    }

    /**
     * JSON 응답
     */
    protected function json($data, $status = 200) {
        json_response($data, $status);
    }

    /**
     * 리다이렉트
     */
    protected function redirect($url) {
        redirect($url);
    }

    /**
     * 레이아웃 설정
     */
    protected function setLayout($layout) {
        $this->template->setLayout($layout);
    }

    /**
     * 레이아웃 없이
     */
    protected function noLayout() {
        $this->template->noLayout();
    }
}
