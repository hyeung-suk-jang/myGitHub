/**
 * 솔루션 마켓 메인 JavaScript
 */

$(document).ready(function() {
    // 알림 메시지 자동 숨김
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // 폼 제출 전 확인
    $('.confirm-submit').on('submit', function(e) {
        if (!confirm('정말 진행하시겠습니까?')) {
            e.preventDefault();
            return false;
        }
    });

    // 이미지 미리보기
    $('input[type="file"][accept*="image"]').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = $(this).siblings('.image-preview');
                if (preview.length === 0) {
                    preview = $('<div class="image-preview"></div>');
                    $(this).after(preview);
                }
                preview.html('<img src="' + e.target.result + '" style="max-width: 200px; margin-top: 10px;">');
            }.bind(this);
            reader.readAsDataURL(file);
        }
    });

    // 검색 폼 엔터키 제출
    $('.search-form input[type="text"]').on('keypress', function(e) {
        if (e.which === 13) {
            $(this).closest('form').submit();
        }
    });
});
