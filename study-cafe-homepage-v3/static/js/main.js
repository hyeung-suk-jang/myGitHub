/**
 * Study Cafe V3 - Main JavaScript
 * Enhanced with Dark Mode, AOS, and improved interactions
 */

$(document).ready(function() {
    let allSeats = [];
    let currentFilter = 'all';
    let currentTheme = localStorage.getItem('theme') || 'light';

    /* ===== Theme Management ===== */
    function initTheme() {
        if (currentTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            $('#themeToggle i').removeClass('fa-moon').addClass('fa-sun');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            $('#themeToggle i').removeClass('fa-sun').addClass('fa-moon');
        }
    }

    $('#themeToggle').click(function() {
        if (currentTheme === 'light') {
            currentTheme = 'dark';
            document.documentElement.setAttribute('data-theme', 'dark');
            $(this).find('i').removeClass('fa-moon').addClass('fa-sun');
        } else {
            currentTheme = 'light';
            document.documentElement.setAttribute('data-theme', 'light');
            $(this).find('i').removeClass('fa-sun').addClass('fa-moon');
        }
        localStorage.setItem('theme', currentTheme);
    });

    /* ===== Scroll Progress Bar ===== */
    $(window).scroll(function() {
        const scrollTop = $(window).scrollTop();
        const docHeight = $(document).height();
        const winHeight = $(window).height();
        const scrollPercent = (scrollTop / (docHeight - winHeight)) * 100;
        $('.scroll-progress').css('width', scrollPercent + '%');

        // Navbar shadow on scroll
        if (scrollTop > 50) {
            $('.navbar').css('box-shadow', '0 4px 20px rgba(0, 0, 0, 0.1)');
        } else {
            $('.navbar').css('box-shadow', '0 2px 4px rgba(0, 0, 0, 0.05)');
        }

        // Back to Top button
        if (scrollTop > 300) {
            $('.back-to-top').addClass('show');
        } else {
            $('.back-to-top').removeClass('show');
        }
    });

    /* ===== Hamburger Menu Toggle ===== */
    $('.hamburger').click(function() {
        $(this).toggleClass('active');
        $('.nav-menu').toggleClass('active');
        $('body').toggleClass('menu-open');
    });

    // Close menu when clicking on a link
    $('.nav-menu a').click(function() {
        $('.nav-menu').removeClass('active');
        $('.hamburger').removeClass('active');
        $('body').removeClass('menu-open');
    });

    /* ===== Smooth Scrolling ===== */
    $('a[href^="#"]').on('click', function(e) {
        const target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 1000, 'swing');
        }
    });

    /* ===== Back to Top Button ===== */
    $('.back-to-top').click(function() {
        $('html, body').animate({ scrollTop: 0 }, 800);
    });

    /* ===== Number Counter Animation ===== */
    function animateCounter(element) {
        const $element = $(element);
        const target = parseInt($element.data('target')) || 0;

        if (target === 0) return;

        $({ countNum: 0 }).animate({
            countNum: target
        }, {
            duration: 2000,
            easing: 'swing',
            step: function() {
                $element.text(Math.floor(this.countNum).toLocaleString());
            },
            complete: function() {
                $element.text(target.toLocaleString());
            }
        });
    }

    // Trigger counter animation when hero section is visible
    let counterAnimated = false;
    $(window).scroll(function() {
        if (!counterAnimated && $('.hero-stats').length) {
            const heroStatsTop = $('.hero-stats').offset().top;
            const scrollTop = $(window).scrollTop() + $(window).height();

            if (scrollTop > heroStatsTop) {
                $('.stat-number[data-target]').each(function() {
                    animateCounter(this);
                });
                counterAnimated = true;
            }
        }
    });

    /* ===== Scroll Animations (AOS-like) ===== */
    function initScrollAnimations() {
        const animateElements = $('[data-aos]');

        function checkVisibility() {
            animateElements.each(function() {
                const $this = $(this);
                const elementTop = $this.offset().top;
                const elementBottom = elementTop + $this.outerHeight();
                const viewportTop = $(window).scrollTop();
                const viewportBottom = viewportTop + $(window).height();

                if (elementBottom > viewportTop && elementTop < viewportBottom) {
                    const animationType = $this.data('aos');
                    const delay = $this.data('aos-delay') || 0;

                    setTimeout(() => {
                        $this.addClass('aos-animate');
                    }, delay);
                }
            });
        }

        // Initial setup
        animateElements.css({
            'opacity': '0',
            'transition': 'all 0.8s ease'
        });

        animateElements.each(function() {
            const $this = $(this);
            const animationType = $this.data('aos');

            switch(animationType) {
                case 'fade-up':
                    $this.css('transform', 'translateY(30px)');
                    break;
                case 'fade-down':
                    $this.css('transform', 'translateY(-30px)');
                    break;
                case 'fade-left':
                    $this.css('transform', 'translateX(30px)');
                    break;
                case 'fade-right':
                    $this.css('transform', 'translateX(-30px)');
                    break;
                case 'zoom-in':
                    $this.css('transform', 'scale(0.8)');
                    break;
                case 'flip-up':
                    $this.css('transform', 'perspective(1000px) rotateX(-30deg)');
                    break;
            }
        });

        // Animate on visibility
        $('.aos-animate').css({
            'opacity': '1',
            'transform': 'none'
        });

        $(window).on('scroll', checkVisibility);
        checkVisibility(); // Initial check
    }

    /* ===== Pricing Toggle ===== */
    $('.toggle-btn').click(function() {
        const type = $(this).data('type');

        $('.toggle-btn').removeClass('active');
        $(this).addClass('active');

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

    /* ===== FAQ Accordion ===== */
    $('.faq-question').click(function() {
        const $item = $(this).closest('.faq-item');
        const $answer = $item.find('.faq-answer');

        // Close other items
        $('.faq-item').not($item).removeClass('active');
        $('.faq-answer').not($answer).slideUp(300);

        // Toggle current item
        $item.toggleClass('active');
        $answer.slideToggle(300);
    });

    /* ===== Seats API Functions ===== */
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
                        <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: var(--error-color);"></i>
                        <p>좌석 정보를 불러오는데 실패했습니다.</p>
                        <button class="btn btn-primary" onclick="location.reload()">다시 시도</button>
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
                    <i class="fas fa-inbox" style="font-size: 3rem; color: var(--text-secondary);"></i>
                    <p>조건에 맞는 좌석이 없습니다.</p>
                </div>
            `);
            return;
        }

        seats.forEach((seat, index) => {
            const seatIcon = getSeatIcon(seat.seat_type);
            const seatTypeName = getSeatTypeName(seat.seat_type);
            const isAvailable = seat.status === 'available';

            const seatElement = $(`
                <div class="seat-item ${seat.status}"
                     data-seat-id="${seat.id}"
                     data-seat-type="${seat.seat_type}"
                     style="animation-delay: ${index * 0.02}s;">
                    <div class="seat-icon">
                        <i class="${seatIcon}"></i>
                    </div>
                    <div class="seat-number">${seat.seat_number}</div>
                    <div class="seat-type">${seatTypeName}</div>
                </div>
            `);

            if (isAvailable) {
                seatElement.click(function() {
                    openReservationModal(seat);
                });
            }

            grid.append(seatElement);
        });

        // Add entrance animation
        setTimeout(() => {
            $('.seat-item').css({
                'animation': 'fadeIn 0.5s ease-out forwards'
            });
        }, 100);
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
                // Animate counters
                animateStatValue($('#availableCount'), stats.available);
                animateStatValue($('#occupiedCount'), stats.occupied);
                animateStatValue($('#reservedCount'), stats.reserved);
                animateStatValue($('#totalCount'), stats.total);
                animateStatValue($('#availableSeats'), stats.available);
            },
            error: function(xhr, status, error) {
                console.error('통계 정보 로드 실패:', error);
            }
        });
    }

    function animateStatValue($element, targetValue) {
        const currentValue = parseInt($element.text()) || 0;
        $({ value: currentValue }).animate({
            value: targetValue
        }, {
            duration: 1000,
            easing: 'swing',
            step: function() {
                $element.text(Math.floor(this.value));
            },
            complete: function() {
                $element.text(targetValue);
            }
        });
    }

    /* ===== Seat Filters ===== */
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

    /* ===== Reservation Modal ===== */
    function openReservationModal(seat) {
        $('#reservationSeatId').val(seat.id);
        $('#reservationSeatNumber').val(seat.seat_number);

        // Set default times
        const now = new Date();
        now.setMinutes(Math.ceil(now.getMinutes() / 30) * 30);
        const startTime = now.toISOString().slice(0, 16);
        $('#reservationStartTime').val(startTime);

        const endTime = new Date(now.getTime() + 2 * 60 * 60 * 1000);
        $('#reservationEndTime').val(endTime.toISOString().slice(0, 16));

        $('#reservationModal').addClass('show');
        $('body').css('overflow', 'hidden');
    }

    function closeReservationModal() {
        $('#reservationModal').removeClass('show');
        $('#reservationForm')[0].reset();
        $('body').css('overflow', '');
    }

    $('.modal-close').click(function() {
        closeReservationModal();
    });

    // Close modal when clicking outside
    $('#reservationModal').click(function(e) {
        if ($(e.target).is('#reservationModal')) {
            closeReservationModal();
        }
    });

    // Close modal with ESC key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            closeReservationModal();
        }
    });

    /* ===== Reservation Form Submit ===== */
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

        const $submitBtn = $(this).find('button[type="submit"]');
        const originalHtml = $submitBtn.html();
        $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> 예약 중...').prop('disabled', true);

        $.ajax({
            url: '/api/reservations',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                showNotification('success', `예약이 완료되었습니다! 예약 번호: ${response.id}`);
                closeReservationModal();
                loadSeats(); // Refresh seats
            },
            error: function(xhr, status, error) {
                let errorMessage = '예약에 실패했습니다.';
                if (xhr.responseJSON && xhr.responseJSON.detail) {
                    errorMessage = xhr.responseJSON.detail;
                }
                showNotification('error', errorMessage);
                console.error('Error:', error);
            },
            complete: function() {
                $submitBtn.html(originalHtml).prop('disabled', false);
            }
        });
    });

    /* ===== Contact Form Submit ===== */
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();

        const formData = {
            name: $('#name').val(),
            phone: $('#phone').val(),
            email: $('#email').val(),
            subject: $('#subject').val(),
            message: $('#message').val()
        };

        const $submitBtn = $(this).find('button[type="submit"]');
        const originalHtml = $submitBtn.html();
        $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> 전송 중...').prop('disabled', true);

        $.ajax({
            url: '/api/contact',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                showNotification('success', '문의가 성공적으로 전송되었습니다!');
                $('#contactForm')[0].reset();
            },
            error: function(xhr, status, error) {
                showNotification('error', '문의 전송에 실패했습니다. 다시 시도해 주세요.');
                console.error('Error:', error);
            },
            complete: function() {
                $submitBtn.html(originalHtml).prop('disabled', false);
            }
        });
    });

    /* ===== Pricing Card Click ===== */
    $('.pricing-card .btn').on('click', function(e) {
        e.preventDefault();

        const $card = $(this).closest('.pricing-card');
        const planName = $card.find('h3').text();
        const price = $card.find('.amount').text();

        showNotification('info', `${planName} (₩${price})을(를) 선택하셨습니다.`);
    });

    /* ===== Chat Button ===== */
    $('#chatBtn').on('click', function() {
        showNotification('info', '채팅 상담 기능은 준비 중입니다. 문의하기를 이용해주세요!');
    });

    /* ===== Notification System ===== */
    function showNotification(type, message) {
        // Remove existing notifications
        $('.notification').remove();

        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };

        const $notification = $(`
            <div class="notification ${type}" style="
                position: fixed;
                top: 100px;
                right: 30px;
                background: var(--bg-primary);
                color: var(--text-primary);
                padding: 1rem 1.5rem;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                display: flex;
                align-items: center;
                gap: 1rem;
                z-index: 10001;
                max-width: 400px;
                animation: slideInRight 0.3s ease-out;
                border-left: 4px solid ${colors[type]};
            ">
                <i class="fas ${icons[type]}" style="font-size: 1.5rem; color: ${colors[type]};"></i>
                <span style="flex: 1;">${message}</span>
                <button class="notification-close" style="
                    background: none;
                    border: none;
                    cursor: pointer;
                    color: var(--text-secondary);
                    font-size: 1.2rem;
                    padding: 0;
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                ">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `);

        $('body').append($notification);

        // Close button
        $notification.find('.notification-close').click(function() {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        });

        // Auto remove after 5 seconds
        setTimeout(() => {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Add slide in animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);

    /* ===== Auto-refresh Seats ===== */
    function startAutoRefresh() {
        setInterval(function() {
            loadSeats();
        }, 30000); // Refresh every 30 seconds
    }

    /* ===== Initialization ===== */
    function init() {
        console.log('🚀 Study Cafe V3 Initialized!');
        console.log('✨ Features: Dark Mode, Real-time Seats, Enhanced UI');

        // Initialize theme
        initTheme();

        // Initialize scroll animations
        initScrollAnimations();

        // Load seats
        loadSeats();

        // Start auto-refresh
        startAutoRefresh();

        // Trigger initial scroll animations
        setTimeout(function() {
            $(window).trigger('scroll');
        }, 100);
    }

    /* ===== Run Initialization ===== */
    init();
});

/* ===== Window Load Event ===== */
$(window).on('load', function() {
    // Hide loading spinner if any
    $('.loader').fadeOut(300);

    // Animate hero content
    $('.hero-content').css('opacity', '1');

    console.log('✅ Page fully loaded');
});

/* ===== Responsive Handling ===== */
$(window).on('resize', function() {
    // Close mobile menu on desktop view
    if ($(window).width() > 768) {
        $('.nav-menu').removeClass('active');
        $('.hamburger').removeClass('active');
        $('body').removeClass('menu-open');
    }
});

/* ===== Service Worker Registration (PWA Support) ===== */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        // Uncomment to enable PWA
        // navigator.serviceWorker.register('/sw.js');
    });
}
