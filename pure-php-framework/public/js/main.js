// 메인 JavaScript 파일

// DOM 로드 완료 시
document.addEventListener('DOMContentLoaded', function() {
    console.log('순수 PHP 프레임워크가 로드되었습니다.');
});

// 삭제 확인 헬퍼
function confirmDelete(message) {
    return confirm(message || '정말 삭제하시겠습니까?');
}
