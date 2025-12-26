$(document).ready(function() {

    /* ===========================
       Navigation
       =========================== */

    // Hamburger menu toggle
    $('#hamburger').click(function() {
        $(this).toggleClass('active');
        $('#nav-menu').toggleClass('active');
    });

    // Close menu when clicking on a nav link
    $('.nav-link').click(function() {
        $('#hamburger').removeClass('active');
        $('#nav-menu').removeClass('active');
    });

    // Active nav link on scroll
    $(window).scroll(function() {
        var scrollPos = $(document).scrollTop();

        $('.nav-link').each(function() {
            var currLink = $(this);
            var refElement = $(currLink.attr("href"));

            if (refElement.length && refElement.position().top <= scrollPos + 100 && refElement.position().top + refElement.height() > scrollPos + 100) {
                $('.nav-link').removeClass("active");
                currLink.addClass("active");
            } else {
                currLink.removeClass("active");
            }
        });
    });

    // Navbar scroll effect
    $(window).scroll(function() {
        if ($(this).scrollTop() > 50) {
            $('.navbar').addClass('scrolled');
        } else {
            $('.navbar').removeClass('scrolled');
        }
    });

    /* ===========================
       Smooth Scrolling
       =========================== */

    $('a[href^="#"]').click(function(e) {
        e.preventDefault();

        var target = $(this.hash);
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 70
            }, 800);
        }
    });

    /* ===========================
       Counter Animation
       =========================== */

    function animateCounter() {
        $('.stat-number').each(function() {
            var $this = $(this);
            var countTo = $this.attr('data-target');

            $({ countNum: 0 }).animate({
                countNum: countTo
            }, {
                duration: 2000,
                easing: 'swing',
                step: function() {
                    $this.text(Math.floor(this.countNum));
                },
                complete: function() {
                    $this.text(this.countNum);
                }
            });
        });
    }

    // Trigger counter animation when visible
    var counterAnimated = false;
    $(window).scroll(function() {
        var aboutOffset = $('#about').offset().top;
        var windowScroll = $(window).scrollTop();
        var windowHeight = $(window).height();

        if (!counterAnimated && windowScroll + windowHeight > aboutOffset + 200) {
            animateCounter();
            counterAnimated = true;
        }
    });

    /* ===========================
       Portfolio Filter
       =========================== */

    $('.filter-btn').click(function() {
        var filterValue = $(this).attr('data-filter');

        $('.filter-btn').removeClass('active');
        $(this).addClass('active');

        if (filterValue === 'all') {
            $('.portfolio-item').fadeIn(300).removeClass('hide');
        } else {
            $('.portfolio-item').each(function() {
                var category = $(this).attr('data-category');
                if (category === filterValue) {
                    $(this).fadeIn(300).removeClass('hide');
                } else {
                    $(this).fadeOut(300).addClass('hide');
                }
            });
        }
    });

    /* ===========================
       Testimonials Slider
       =========================== */

    var currentTestimonial = 0;
    var testimonialItems = $('.testimonial-item');
    var totalTestimonials = testimonialItems.length;

    function showTestimonial(index) {
        testimonialItems.removeClass('active');
        $(testimonialItems[index]).addClass('active');
    }

    // Show first testimonial
    showTestimonial(0);

    // Next button
    $('.next-btn').click(function() {
        currentTestimonial = (currentTestimonial + 1) % totalTestimonials;
        showTestimonial(currentTestimonial);
    });

    // Previous button
    $('.prev-btn').click(function() {
        currentTestimonial = (currentTestimonial - 1 + totalTestimonials) % totalTestimonials;
        showTestimonial(currentTestimonial);
    });

    // Auto slide every 5 seconds
    setInterval(function() {
        currentTestimonial = (currentTestimonial + 1) % totalTestimonials;
        showTestimonial(currentTestimonial);
    }, 5000);

    /* ===========================
       Contact Form
       =========================== */

    $('#contact-form').submit(function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.text();

        // Disable submit button and show loading
        submitBtn.prop('disabled', true).text('전송 중...');
        $('#form-message').removeClass('success error').hide();

        $.ajax({
            type: 'POST',
            url: 'php/contact.php',
            data: formData,
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).text(originalText);

                if (response.success) {
                    $('#form-message')
                        .addClass('success')
                        .text(response.message)
                        .fadeIn();
                    $('#contact-form')[0].reset();
                } else {
                    $('#form-message')
                        .addClass('error')
                        .text(response.message)
                        .fadeIn();
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).text(originalText);
                $('#form-message')
                    .addClass('error')
                    .text('오류가 발생했습니다. 다시 시도해주세요.')
                    .fadeIn();
            }
        });
    });

    /* ===========================
       Scroll to Top Button
       =========================== */

    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('#scroll-top').addClass('show');
        } else {
            $('#scroll-top').removeClass('show');
        }
    });

    $('#scroll-top').click(function() {
        $('html, body').animate({
            scrollTop: 0
        }, 800);
    });

    /* ===========================
       Scroll Animations
       =========================== */

    function isElementInViewport(el) {
        var rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    function isElementPartiallyInViewport(el) {
        var rect = el.getBoundingClientRect();
        var windowHeight = (window.innerHeight || document.documentElement.clientHeight);
        var windowWidth = (window.innerWidth || document.documentElement.clientWidth);

        var vertInView = (rect.top <= windowHeight) && ((rect.top + rect.height) >= 0);
        var horInView = (rect.left <= windowWidth) && ((rect.left + rect.width) >= 0);

        return (vertInView && horInView);
    }

    function animateOnScroll() {
        $('.service-card, .portfolio-item, .testimonial-content').each(function() {
            if (isElementPartiallyInViewport(this)) {
                $(this).addClass('animate-in');
            }
        });
    }

    $(window).scroll(animateOnScroll);
    animateOnScroll(); // Run on page load

    /* ===========================
       Page Load Animation
       =========================== */

    $(window).on('load', function() {
        $('body').addClass('loaded');
    });

    /* ===========================
       Parallax Effect (Optional)
       =========================== */

    $(window).scroll(function() {
        var scrolled = $(window).scrollTop();
        $('.hero').css('background-position', 'center ' + (scrolled * 0.5) + 'px');
    });

    /* ===========================
       Form Validation
       =========================== */

    $('input, textarea').on('blur', function() {
        var $this = $(this);

        if ($this.val().trim() === '') {
            $this.css('border-color', '#ef4444');
        } else {
            $this.css('border-color', '#e5e7eb');
        }
    });

    // Email validation
    $('#email').on('blur', function() {
        var email = $(this).val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailRegex.test(email)) {
            $(this).css('border-color', '#ef4444');
        } else {
            $(this).css('border-color', '#10b981');
        }
    });

    /* ===========================
       Prevent Default Link Behavior
       =========================== */

    $('.portfolio-link').click(function(e) {
        e.preventDefault();
        // Add your modal or lightbox functionality here
        alert('포트폴리오 상세 페이지로 이동합니다.');
    });

    /* ===========================
       Social Links (Optional)
       =========================== */

    $('.social-link').click(function(e) {
        e.preventDefault();
        var socialPlatform = $(this).find('i').attr('class');
        console.log('Social platform clicked: ' + socialPlatform);
        // Add actual social media links here
    });

    /* ===========================
       Loading Screen (Optional)
       =========================== */

    setTimeout(function() {
        $('.hero-content').css('opacity', '1');
    }, 100);

    /* ===========================
       Responsive Menu Close on Outside Click
       =========================== */

    $(document).click(function(e) {
        var container = $(".navbar");
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            $('#hamburger').removeClass('active');
            $('#nav-menu').removeClass('active');
        }
    });

    /* ===========================
       Add Animation Classes to Elements
       =========================== */

    $('.service-card').each(function(index) {
        $(this).css('animation-delay', (index * 0.1) + 's');
    });

    $('.portfolio-item').each(function(index) {
        $(this).css('animation-delay', (index * 0.1) + 's');
    });

});

/* ===========================
   Vanilla JS for Better Performance
   =========================== */

// Preload images
window.addEventListener('load', function() {
    const images = document.querySelectorAll('img[data-src]');
    images.forEach(img => {
        img.setAttribute('src', img.getAttribute('data-src'));
        img.onload = function() {
            img.removeAttribute('data-src');
        };
    });
});

// Intersection Observer for animations
if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });

    document.addEventListener('DOMContentLoaded', function() {
        const animatedElements = document.querySelectorAll('.service-card, .portfolio-item');
        animatedElements.forEach(el => observer.observe(el));
    });
}
