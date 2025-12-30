<?php
/**
 * 홈 컨트롤러
 */

require_once CORE_PATH . '/controller.php';

class HomeController extends Controller {
    /**
     * 홈페이지
     */
    public function index() {
        $data = [
            'title' => '홈',
            'message' => '순수 PHP 프레임워크에 오신 것을 환영합니다!',
            'features' => [
                'Composer 없이 순수 PHP만 사용',
                '단순한 라우팅 시스템',
                'PDO 기반 데이터베이스 래퍼',
                '순수 PHP 템플릿 엔진',
                'CSRF 보호',
                '세션 관리',
                '파일 업로드'
            ]
        ];

        $this->view('home/index', $data);
    }
}
