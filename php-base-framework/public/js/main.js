/**
 * Modern Corporate Website - Main JavaScript
 * Using jQuery for interactive features
 */

(function($) {
    'use strict';

    // ========================================
    // Navigation & Header
    // ========================================

    // Header scroll effect
    function handleHeaderScroll() {
        const header = $('header');
        if ($(window).scrollTop() > 50) {
            header.addClass('scrolled');
        } else {
            header.removeClass('scrolled');
        }
    }

    // Mobile menu toggle
    function initMobileMenu() {
        const mobileToggle = $('.mobile-menu-toggle');
        const navbarMenu = $('.navbar-menu');

        // Create mobile toggle if it doesn't exist
        if (mobileToggle.length === 0) {
            $('.navbar-user').before(`
                <div class="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            `);
        }

        // Toggle menu on click
        $(document).on('click', '.mobile-menu-toggle', function() {
            $(this).toggleClass('active');
            navbarMenu.toggleClass('active');
            $('body').toggleClass('menu-open');
        });

        // Close menu when clicking on a link
        $('.navbar-menu a').on('click', function() {
            $('.mobile-menu-toggle').removeClass('active');
            navbarMenu.removeClass('active');
            $('body').removeClass('menu-open');
        });

        // Close menu when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.navbar-menu, .mobile-menu-toggle').length) {
                $('.mobile-menu-toggle').removeClass('active');
                navbarMenu.removeClass('active');
                $('body').removeClass('menu-open');
            }
        });
    }

    // Smooth scroll for anchor links
    function initSmoothScroll() {
        $('a[href^="#"]').on('click', function(e) {
            const target = $(this.hash);
            if (target.length) {
                e.preventDefault();
                const offset = 80; // Header height
                $('html, body').animate({
                    scrollTop: target.offset().top - offset
                }, 800, 'swing');
            }
        });
    }

    // ========================================
    // Scroll Animations
    // ========================================

    function initScrollAnimations() {
        // Add scroll-animate class to elements
        $('.service-card, .stat-card, .portfolio-item').addClass('scroll-animate');

        // Check if element is in viewport
        function isInViewport(element) {
            const elementTop = $(element).offset().top;
            const elementBottom = elementTop + $(element).outerHeight();
            const viewportTop = $(window).scrollTop();
            const viewportBottom = viewportTop + $(window).height();
            return elementBottom > viewportTop && elementTop < viewportBottom - 100;
        }

        // Trigger animation when scrolling
        function checkScroll() {
            $('.scroll-animate').each(function() {
                if (isInViewport(this)) {
                    $(this).addClass('active');
                }
            });
        }

        // Initial check
        checkScroll();

        // Check on scroll
        $(window).on('scroll', function() {
            checkScroll();
        });
    }

    // ========================================
    // Counter Animation
    // ========================================

    function initCounterAnimation() {
        let counterAnimated = false;

        function animateCounters() {
            if (counterAnimated) return;

            const statsSection = $('.stats-grid');
            if (statsSection.length && isElementInViewport(statsSection[0])) {
                counterAnimated = true;

                $('.stat-number').each(function() {
                    const $this = $(this);
                    const countTo = parseInt($this.attr('data-count'));

                    $({ countNum: 0 }).animate(
                        { countNum: countTo },
                        {
                            duration: 2000,
                            easing: 'swing',
                            step: function() {
                                $this.text(Math.floor(this.countNum));
                            },
                            complete: function() {
                                $this.text(this.countNum);
                            }
                        }
                    );
                });
            }
        }

        function isElementInViewport(el) {
            const rect = el.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }

        $(window).on('scroll', animateCounters);
        animateCounters(); // Check on load
    }

    // ========================================
    // Contact Form
    // ========================================

    function initContactForm() {
        $('#contactForm').on('submit', function(e) {
            e.preventDefault();

            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.text();

            // Disable submit button
            submitBtn.prop('disabled', true).text('전송 중...');

            // Simulate form submission (replace with actual AJAX call)
            setTimeout(function() {
                // Success message
                alert('상담 신청이 완료되었습니다. 빠른 시일 내에 연락드리겠습니다.');

                // Reset form
                form[0].reset();

                // Re-enable submit button
                submitBtn.prop('disabled', false).text(originalText);
            }, 1500);

            // Uncomment below for actual AJAX submission
            /*
            $.ajax({
                url: '/api/contact',
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    alert('상담 신청이 완료되었습니다. 빠른 시일 내에 연락드리겠습니다.');
                    form[0].reset();
                },
                error: function(xhr, status, error) {
                    alert('오류가 발생했습니다. 다시 시도해주세요.');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
            */
        });
    }

    // ========================================
    // Parallax Effect (Optional)
    // ========================================

    function initParallax() {
        $(window).on('scroll', function() {
            const scrolled = $(window).scrollTop();
            $('.hero-content').css('transform', 'translateY(' + (scrolled * 0.3) + 'px)');
        });
    }

    // ========================================
    // Service Card Hover Effect
    // ========================================

    function initServiceCardEffect() {
        $('.service-card').on('mouseenter', function() {
            $(this).find('.service-icon').css('transform', 'rotate(360deg)');
        }).on('mouseleave', function() {
            $(this).find('.service-icon').css('transform', 'rotate(0deg)');
        });

        // Add transition to service icons
        $('.service-icon').css('transition', 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)');
    }

    // ========================================
    // Lazy Loading Images (Optional)
    // ========================================

    function initLazyLoad() {
        $('img[data-src]').each(function() {
            const img = $(this);
            const src = img.attr('data-src');

            if (isInViewport(this)) {
                img.attr('src', src).removeAttr('data-src');
            }
        });

        function isInViewport(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }

        $(window).on('scroll resize', function() {
            $('img[data-src]').each(function() {
                if (isInViewport(this)) {
                    const img = $(this);
                    const src = img.attr('data-src');
                    img.attr('src', src).removeAttr('data-src');
                }
            });
        });
    }

    // ========================================
    // Active Navigation Link
    // ========================================

    function initActiveNavLink() {
        $(window).on('scroll', function() {
            const scrollPos = $(document).scrollTop() + 100;

            $('.navbar-menu a').each(function() {
                const currLink = $(this);
                const refElement = $(currLink.attr('href'));

                if (refElement.length && refElement.position().top <= scrollPos &&
                    refElement.position().top + refElement.height() > scrollPos) {
                    $('.navbar-menu a').removeClass('active');
                    currLink.addClass('active');
                } else {
                    currLink.removeClass('active');
                }
            });
        });
    }

    // ========================================
    // Back to Top Button (Optional)
    // ========================================

    function initBackToTop() {
        // Create back to top button
        $('body').append('<button id="backToTop" style="display:none;">↑</button>');

        // Style the button
        $('#backToTop').css({
            'position': 'fixed',
            'bottom': '30px',
            'right': '30px',
            'width': '50px',
            'height': '50px',
            'border-radius': '50%',
            'background': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'color': '#fff',
            'border': 'none',
            'font-size': '24px',
            'cursor': 'pointer',
            'z-index': '999',
            'box-shadow': '0 4px 6px rgba(0,0,0,0.1)',
            'transition': 'all 0.3s ease'
        });

        // Show/hide button
        $(window).on('scroll', function() {
            if ($(this).scrollTop() > 300) {
                $('#backToTop').fadeIn();
            } else {
                $('#backToTop').fadeOut();
            }
        });

        // Scroll to top on click
        $('#backToTop').on('click', function() {
            $('html, body').animate({ scrollTop: 0 }, 800);
        });

        // Hover effect
        $('#backToTop').on('mouseenter', function() {
            $(this).css('transform', 'translateY(-5px)');
        }).on('mouseleave', function() {
            $(this).css('transform', 'translateY(0)');
        });
    }

    // ========================================
    // Initialize All Functions
    // ========================================

    $(document).ready(function() {
        // Initialize navigation
        initMobileMenu();
        initSmoothScroll();
        initActiveNavLink();

        // Initialize animations
        initScrollAnimations();
        initCounterAnimation();
        initParallax();

        // Initialize interactions
        initServiceCardEffect();
        initContactForm();
        initLazyLoad();
        initBackToTop();

        // Header scroll effect
        $(window).on('scroll', handleHeaderScroll);
        handleHeaderScroll(); // Initial check
    });

    // ========================================
    // Performance: Debounce Scroll Events
    // ========================================

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Apply debounce to scroll events
    $(window).on('scroll', debounce(function() {
        // Your scroll event handlers here
    }, 10));

})(jQuery);
