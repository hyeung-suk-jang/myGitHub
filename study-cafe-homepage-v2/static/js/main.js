$(document).ready(function() {
    let allSeats = [];
    let currentFilter = 'all';

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
        $('.feature-card, .facility-card, .pricing-card, .info-card, .concept-card, .gallery-item').each(function() {
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
    $('.feature-card, .facility-card, .pricing-card, .info-card, .concept-card, .gallery-item').css({
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

    /* ===== 좌석 현황 API 호출 ===== */
    function loadSeats() {
        $.ajax({
            url: '/api/seats',
            method: 'GET',
            success: function(seats) {
                allSeats = seats;
                displaySeats(seats);
                updateStatistics();
            },
            error: function(xhr, status, error) {
                console.error('좌석 정보 로드 실패:', error);
                $('#seatsGrid').html(`
                    <div class="loading-spinner">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>좌석 정보를 불러오는데 실패했습니다.</p>
                    </div>
                `);
            }
        });
    }

    function displaySeats(seats) {
        const grid = $('#seatsGrid');
        grid.empty();

        if (seats.length === 0) {
            grid.html(`
                <div class="loading-spinner">
                    <p>조건에 맞는 좌석이 없습니다.</p>
                </div>
            `);
            return;
        }

        seats.forEach(seat => {
            const seatIcon = getSeatIcon(seat.seat_type);
            const seatTypeName = getSeatTypeName(seat.seat_type);
            const isAvailable = seat.status === 'available';

            const seatElement = $(`
                <div class="seat-item ${seat.status}" data-seat-id="${seat.id}" data-seat-type="${seat.seat_type}">
                    <div class="seat-status"></div>
                    <div class="seat-icon">
                        <i class="${seatIcon}"></i>
                    </div>
                    <div class="seat-number">${seat.seat_number}</div>
                    <div class="seat-type">${seatTypeName}</div>
                </div>
            `);

            // 이용 가능한 좌석만 클릭 가능
            if (isAvailable) {
                seatElement.click(function() {
                    openReservationModal(seat);
                });
            }

            grid.append(seatElement);
        });
    }

    function getSeatIcon(type) {
        switch(type) {
            case 'fixed': return 'fas fa-chair';
            case 'free': return 'fas fa-couch';
            case 'study_room': return 'fas fa-users';
            default: return 'fas fa-chair';
        }
    }

    function getSeatTypeName(type) {
        switch(type) {
            case 'fixed': return '고정석';
            case 'free': return '자유석';
            case 'study_room': return '스터디룸';
            default: return '좌석';
        }
    }

    function updateStatistics() {
        $.ajax({
            url: '/api/seats/statistics',
            method: 'GET',
            success: function(stats) {
                $('#availableCount').text(stats.available);
                $('#occupiedCount').text(stats.occupied);
                $('#reservedCount').text(stats.reserved);
                $('#totalCount').text(stats.total);
                $('#availableSeats').text(stats.available);
            },
            error: function(xhr, status, error) {
                console.error('통계 정보 로드 실패:', error);
            }
        });
    }

    /* ===== 좌석 필터 ===== */
    $('.filter-btn').click(function() {
        const filter = $(this).data('filter');
        currentFilter = filter;

        $('.filter-btn').removeClass('active');
        $(this).addClass('active');

        let filteredSeats = allSeats;
        if (filter !== 'all') {
            filteredSeats = allSeats.filter(seat => seat.seat_type === filter);
        }

        displaySeats(filteredSeats);
    });

    /* ===== 예약 모달 ===== */
    function openReservationModal(seat) {
        $('#reservationSeatId').val(seat.id);
        $('#reservationSeatNumber').val(seat.seat_number);

        // 시작 시간 기본값: 현재 시간
        const now = new Date();
        now.setMinutes(Math.ceil(now.getMinutes() / 30) * 30); // 30분 단위로 반올림
        const startTime = now.toISOString().slice(0, 16);
        $('#reservationStartTime').val(startTime);

        // 종료 시간 기본값: 2시간 후
        const endTime = new Date(now.getTime() + 2 * 60 * 60 * 1000);
        $('#reservationEndTime').val(endTime.toISOString().slice(0, 16));

        $('#reservationModal').addClass('show');
    }

    function closeReservationModal() {
        $('#reservationModal').removeClass('show');
        $('#reservationForm')[0].reset();
    }

    $('.modal-close').click(function() {
        closeReservationModal();
    });

    // 모달 외부 클릭 시 닫기
    $('#reservationModal').click(function(e) {
        if ($(e.target).is('#reservationModal')) {
            closeReservationModal();
        }
    });

    /* ===== 예약 폼 제출 ===== */
    $('#reservationForm').on('submit', function(e) {
        e.preventDefault();

        const formData = {
            seat_id: parseInt($('#reservationSeatId').val()),
            user_name: $('#reservationName').val(),
            user_phone: $('#reservationPhone').val(),
            user_email: $('#reservationEmail').val(),
            start_time: $('#reservationStartTime').val() + ':00',
            end_time: $('#reservationEndTime').val() + ':00'
        };

        // 버튼 로딩 상태
        const $submitBtn = $(this).find('button[type="submit"]');
        const originalText = $submitBtn.html();
        $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> 예약 중...').prop('disabled', true);

        $.ajax({
            url: '/api/reservations',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                alert(`예약이 완료되었습니다!\n\n예약 번호: ${response.id}\n좌석: ${$('#reservationSeatNumber').val()}\n이름: ${response.user_name}\n시작: ${new Date(response.start_time).toLocaleString()}\n종료: ${new Date(response.end_time).toLocaleString()}`);
                closeReservationModal();
                loadSeats(); // 좌석 현황 새로고침
            },
            error: function(xhr, status, error) {
                let errorMessage = '예약에 실패했습니다.';
                if (xhr.responseJSON && xhr.responseJSON.detail) {
                    errorMessage = xhr.responseJSON.detail;
                }
                alert(errorMessage);
                console.error('Error:', error);
            },
            complete: function() {
                $submitBtn.html(originalText).prop('disabled', false);
            }
        });
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

        alert(`${planName} (₩${price})을(를) 선택하셨습니다.\n결제 페이지로 이동합니다.`);
    });

    /* ===== 이미지 Lazy Loading ===== */
    $('img').each(function() {
        const $img = $(this);
        $img.on('load', function() {
            $img.css('opacity', '1');
        });
    });

    /* ===== 스크롤 진행 표시 ===== */
    function updateScrollProgress() {
        const scrollTop = $(window).scrollTop();
        const docHeight = $(document).height();
        const winHeight = $(window).height();
        const scrollPercent = (scrollTop / (docHeight - winHeight)) * 100;
    }

    $(window).on('scroll', updateScrollProgress);

    /* ===== 실시간 좌석 현황 업데이트 ===== */
    function refreshSeats() {
        loadSeats();
    }

    // 30초마다 좌석 현황 자동 새로고침
    setInterval(refreshSeats, 30000);

    /* ===== 초기화 ===== */
    console.log('Study Cafe Homepage V2 initialized!');

    // 페이지 로드 시 좌석 정보 로드
    loadSeats();

    // 페이지 로드 시 스크롤 위치에 따른 애니메이션 실행
    setTimeout(function() {
        $(window).trigger('scroll');
    }, 100);
});

/* ===== 페이지 로드 완료 후 실행 ===== */
$(window).on('load', function() {
    // 로딩 스피너 제거
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

/* ===== 키보드 이벤트 ===== */
$(document).on('keydown', function(e) {
    // ESC 키로 모달 닫기
    if (e.key === 'Escape') {
        $('#reservationModal').removeClass('show');
    }
});
