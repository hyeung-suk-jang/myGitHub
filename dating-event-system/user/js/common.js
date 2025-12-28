// 공통 JavaScript 함수

$(document).ready(function() {
    // 자동 새로고침 (선택 페이지 등)
    if (window.location.pathname.includes('selection.php') ||
        window.location.pathname.includes('matching.php')) {
        // 2분마다 자동 새로고침 (선택 시간 확인용)
        // setTimeout(function() {
        //     location.reload();
        // }, 120000);
    }

    // 폼 제출 전 확인
    $('form').on('submit', function(e) {
        var $submitBtn = $(this).find('button[type="submit"]');
        if ($submitBtn.data('confirm')) {
            if (!confirm($submitBtn.data('confirm'))) {
                e.preventDefault();
                return false;
            }
        }
    });

    // 전화번호 자동 포맷팅
    $('input[type="tel"]').on('input', function() {
        var val = $(this).val().replace(/[^0-9]/g, '');
        if (val.length > 11) val = val.substring(0, 11);
        $(this).val(val);
    });

    // 알림 메시지 자동 숨김
    $('.alert').each(function() {
        var $this = $(this);
        setTimeout(function() {
            $this.fadeOut();
        }, 5000);
    });
});

// 유틸리티 함수
function formatPhoneNumber(phone) {
    phone = phone.replace(/[^0-9]/g, '');
    if (phone.length === 10) {
        return phone.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
    } else if (phone.length === 11) {
        return phone.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
    }
    return phone;
}

function formatMoney(amount) {
    return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '원';
}
