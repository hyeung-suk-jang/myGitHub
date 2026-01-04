$(document).ready(function() {

    /* ===== 네비게이션 스크롤 효과 ===== */
    $(window).scroll(function() {
        if ($(this).scrollTop() > 50) {
            $('.navbar').css('box-shadow', '0 4px 12px rgba(0, 0, 0, 0.15)');
        } else {
            $('.navbar').css('box-shadow', '0 2px 4px rgba(0, 0, 0, 0.1)');
        }

        // Back to Top 버튼 표시/숨김
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').addClass('show');
        } else {
            $('.back-to-top').removeClass('show');
        }
    });

    /* ===== 햄버거 메뉴 토글 ===== */
    $('.hamburger').click(function() {
        $('.nav-menu').toggleClass('active');
        $(this).toggleClass('active');
    });

    // 메뉴 아이템 클릭 시 모바일 메뉴 닫기
    $('.nav-menu a').click(function() {
        $('.nav-menu').removeClass('active');
        $('.hamburger').removeClass('active');
    });

    /* ===== 부드러운 스크롤 ===== */
    $('a[href^="#"]').on('click', function(e) {
        const target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 70
            }, 1000);
        }
    });

    /* ===== Back to Top 버튼 ===== */
    $('.back-to-top').click(function() {
        $('html, body').animate({ scrollTop: 0 }, 800);
    });

    /* ===== 가격 토글 ===== */
    $('.toggle-btn').click(function() {
        const type = $(this).data('type');

        // 버튼 활성화 상태 변경
        $('.toggle-btn').removeClass('active');
        $(this).addClass('active');

        // 가격표 전환 애니메이션
        $('.pricing-grid').fadeOut(300, function() {
            if (type === 'time') {
                $('#time-pricing').fadeIn(300);
                $('#fixed-pricing').hide();
            } else {
                $('#fixed-pricing').fadeIn(300);
                $('#time-pricing').hide();
            }
        });
    });

    /* ===== 스크롤 애니메이션 ===== */
    function animateOnScroll() {
        $('.feature-card, .facility-card, .pricing-card, .info-card').each(function() {
            const elementTop = $(this).offset().top;
            const elementBottom = elementTop + $(this).outerHeight();
            const viewportTop = $(window).scrollTop();
            const viewportBottom = viewportTop + $(window).height();

            if (elementBottom > viewportTop && elementTop < viewportBottom) {
                $(this).css({
                    'opacity': '1',
                    'transform': 'translateY(0)'
                });
            }
        });
    }

    // 초기 상태 설정
    $('.feature-card, .facility-card, .pricing-card, .info-card').css({
        'opacity': '0',
        'transform': 'translateY(30px)',
        'transition': 'all 0.6s ease'
    });

    // 스크롤 이벤트
    $(window).on('scroll', animateOnScroll);
    // 페이지 로드 시 실행
    animateOnScroll();

    /* ===== 숫자 카운트업 애니메이션 ===== */
    function countUp() {
        $('.stat-number').each(function() {
            const $this = $(this);
            const text = $this.text();

            // 숫자만 추출
            const match = text.match(/\d+/);
            if (!match) return;

            const countTo = parseInt(match[0]);
            const prefix = text.split(match[0])[0];
            const suffix = text.split(match[0])[1];

            $({ countNum: 0 }).animate({
                countNum: countTo
            }, {
                duration: 2000,
                easing: 'swing',
                step: function() {
                    $this.text(prefix + Math.floor(this.countNum) + suffix);
                },
                complete: function() {
                    $this.text(prefix + this.countNum + suffix);
                }
            });
        });
    }

    // 히어로 섹션이 보일 때 카운트업 실행
    let counted = false;
    $(window).scroll(function() {
        if (!counted && $('.hero-stats').length) {
            const heroStatsTop = $('.hero-stats').offset().top;
            const scrollTop = $(window).scrollTop() + $(window).height();

            if (scrollTop > heroStatsTop) {
                countUp();
                counted = true;
            }
        }
    });

    /* ===== 문의 폼 제출 ===== */
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();

        const formData = {
            name: $('#name').val(),
            phone: $('#phone').val(),
            email: $('#email').val(),
            subject: $('#subject').val(),
            message: $('#message').val()
        };

        // 버튼 로딩 상태
        const $submitBtn = $(this).find('button[type="submit"]');
        const originalText = $submitBtn.html();
        $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> 전송 중...').prop('disabled', true);

        // AJAX 요청
        $.ajax({
            url: '/api/contact',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                alert('문의가 성공적으로 전송되었습니다! 빠른 시일 내에 답변 드리겠습니다.');
                $('#contactForm')[0].reset();
            },
            error: function(xhr, status, error) {
                alert('문의 전송에 실패했습니다. 다시 시도해 주세요.');
                console.error('Error:', error);
            },
            complete: function() {
                // 버튼 원래 상태로 복구
                $submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    /* ===== 가격 카드 클릭 이벤트 ===== */
    $('.pricing-card .btn').on('click', function(e) {
        e.preventDefault();

        const $card = $(this).closest('.pricing-card');
        const planName = $card.find('h3').text();
        const price = $card.find('.amount').text();

        // 실제로는 결제 페이지로 이동하거나 모달을 띄울 수 있습니다
        alert(`${planName} (₩${price})을(를) 선택하셨습니다.\n결제 페이지로 이동합니다.`);

        // 예: window.location.href = '/payment?plan=' + planName;
    });

    /* ===== 이미지 Lazy Loading ===== */
    $('img').each(function() {
        const $img = $(this);
        $img.on('load', function() {
            $img.css('opacity', '1');
        });
    });

    /* ===== 툴팁 기능 (선택사항) ===== */
    $('[data-tooltip]').hover(
        function() {
            const tooltipText = $(this).data('tooltip');
            $('<div class="tooltip">' + tooltipText + '</div>')
                .appendTo('body')
                .fadeIn(200);
        },
        function() {
            $('.tooltip').remove();
        }
    ).mousemove(function(e) {
        $('.tooltip').css({
            top: e.pageY + 10 + 'px',
            left: e.pageX + 10 + 'px'
        });
    });

    /* ===== 스크롤 진행 표시 (선택사항) ===== */
    function updateScrollProgress() {
        const scrollTop = $(window).scrollTop();
        const docHeight = $(document).height();
        const winHeight = $(window).height();
        const scrollPercent = (scrollTop / (docHeight - winHeight)) * 100;

        // 진행 바를 추가하려면 HTML에 요소를 추가하고 여기서 업데이트
        // $('#scroll-progress').css('width', scrollPercent + '%');
    }

    $(window).on('scroll', updateScrollProgress);

    /* ===== 초기화 ===== */
    console.log('Study Cafe Homepage initialized!');

    // 페이지 로드 시 스크롤 위치에 따른 애니메이션 실행
    setTimeout(function() {
        $(window).trigger('scroll');
    }, 100);
});

/* ===== 페이지 로드 완료 후 실행 ===== */
$(window).on('load', function() {
    // 로딩 스피너 제거 (있는 경우)
    $('.loader').fadeOut(300);

    // 히어로 섹션 애니메이션
    $('.hero-content').css('opacity', '1');
});

/* ===== 반응형 처리 ===== */
$(window).on('resize', function() {
    // 창 크기 변경 시 메뉴 닫기
    if ($(window).width() > 768) {
        $('.nav-menu').removeClass('active');
        $('.hamburger').removeClass('active');
    }
});
