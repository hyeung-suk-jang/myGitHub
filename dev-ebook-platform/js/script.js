// 개발자 전용 전자책 사이트 JavaScript

$(document).ready(function() {
    // 검색 기능
    let searchTimeout;
    $('#search-input').on('keyup', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();

        if (query.length >= 2) {
            searchTimeout = setTimeout(function() {
                searchEbooks(query);
            }, 500);
        }
    });

    // 실시간 검색
    function searchEbooks(query) {
        $.ajax({
            url: 'php/search.php',
            type: 'GET',
            data: { q: query },
            success: function(response) {
                displaySearchResults(response);
            }
        });
    }

    // 검색 결과 표시
    function displaySearchResults(results) {
        const resultsContainer = $('#search-results');
        resultsContainer.empty();

        if (results.length > 0) {
            results.forEach(function(book) {
                const item = `
                    <div class="search-result-item" data-id="${book.id}">
                        <img src="${book.cover_image || 'images/default-cover.jpg'}" alt="${book.title}">
                        <div>
                            <h4>${book.title}</h4>
                            <p>${book.author_name}</p>
                            <span class="price">${formatPrice(book.price)}</span>
                        </div>
                    </div>
                `;
                resultsContainer.append(item);
            });
            resultsContainer.show();
        } else {
            resultsContainer.hide();
        }
    }

    // 장바구니에 추가
    $('.btn-add-cart').on('click', function(e) {
        e.preventDefault();
        const ebookId = $(this).data('id');

        $.ajax({
            url: 'php/cart_add.php',
            type: 'POST',
            data: { ebook_id: ebookId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('장바구니에 추가되었습니다!', 'success');
                    updateCartCount();
                } else {
                    showNotification(response.message || '오류가 발생했습니다.', 'error');
                }
            },
            error: function() {
                showNotification('로그인이 필요합니다.', 'error');
            }
        });
    });

    // 장바구니 개수 업데이트
    function updateCartCount() {
        $.ajax({
            url: 'php/cart_count.php',
            type: 'GET',
            success: function(response) {
                if (response.count > 0) {
                    $('.cart-count').text(response.count).show();
                } else {
                    $('.cart-count').hide();
                }
            }
        });
    }

    // 필터 변경 시
    $('.filter-select').on('change', function() {
        applyFilters();
    });

    // 필터 적용
    function applyFilters() {
        const category = $('#filter-category').val();
        const skillLevel = $('#filter-skill').val();
        const tag = $('#filter-tag').val();
        const order = $('#filter-order').val();

        const params = {
            category: category,
            skill_level: skillLevel,
            tag: tag,
            order: order
        };

        window.location.href = 'books.php?' + $.param(params);
    }

    // 태그 필터링
    $('.tag').on('click', function(e) {
        e.preventDefault();
        const tag = $(this).data('tag');
        $('.tag').removeClass('active');
        $(this).addClass('active');

        $('#filter-tag').val(tag);
        applyFilters();
    });

    // 별점 표시
    function displayStars(rating) {
        let stars = '';
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;

        for (let i = 0; i < fullStars; i++) {
            stars += '★';
        }
        if (hasHalfStar) {
            stars += '⯨';
        }
        const emptyStars = 5 - Math.ceil(rating);
        for (let i = 0; i < emptyStars; i++) {
            stars += '☆';
        }

        return stars;
    }

    // 리뷰 작성
    $('#review-form').on('submit', function(e) {
        e.preventDefault();

        const formData = {
            ebook_id: $(this).data('ebook-id'),
            rating: $('#rating').val(),
            code_quality_rating: $('#code-quality-rating').val(),
            content_rating: $('#content-rating').val(),
            comment: $('#review-comment').val()
        };

        $.ajax({
            url: 'php/review_add.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('리뷰가 등록되었습니다!', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification(response.message, 'error');
                }
            }
        });
    });

    // 별점 입력 인터페이스
    $('.rating-input').each(function() {
        const container = $(this);
        const input = container.find('input');
        let stars = '';

        for (let i = 1; i <= 5; i++) {
            stars += `<span class="star" data-value="${i}">☆</span>`;
        }

        container.find('.stars-display').html(stars);

        container.find('.star').on('click', function() {
            const value = $(this).data('value');
            input.val(value);
            updateStarDisplay(container, value);
        });

        container.find('.star').on('mouseenter', function() {
            const value = $(this).data('value');
            updateStarDisplay(container, value);
        });

        container.on('mouseleave', function() {
            const value = input.val() || 0;
            updateStarDisplay(container, value);
        });
    });

    function updateStarDisplay(container, rating) {
        container.find('.star').each(function(index) {
            if (index < rating) {
                $(this).text('★');
            } else {
                $(this).text('☆');
            }
        });
    }

    // 알림 표시
    function showNotification(message, type) {
        const notification = $('<div>')
            .addClass('notification')
            .addClass('notification-' + type)
            .text(message)
            .css({
                position: 'fixed',
                top: '20px',
                right: '20px',
                padding: '15px 25px',
                borderRadius: '8px',
                backgroundColor: type === 'success' ? '#10b981' : '#ef4444',
                color: 'white',
                fontWeight: '600',
                boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)',
                zIndex: 9999,
                animation: 'slideIn 0.3s ease'
            });

        $('body').append(notification);

        setTimeout(function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

    // 가격 포맷팅
    function formatPrice(price) {
        return new Intl.NumberFormat('ko-KR', {
            style: 'currency',
            currency: 'KRW'
        }).format(price);
    }

    // 무한 스크롤
    let page = 1;
    let loading = false;
    let hasMore = true;

    $(window).on('scroll', function() {
        if (!loading && hasMore) {
            const scrollTop = $(window).scrollTop();
            const windowHeight = $(window).height();
            const documentHeight = $(document).height();

            if (scrollTop + windowHeight > documentHeight - 500) {
                loadMoreBooks();
            }
        }
    });

    function loadMoreBooks() {
        loading = true;
        page++;

        $.ajax({
            url: 'php/load_more.php',
            type: 'GET',
            data: { page: page },
            dataType: 'json',
            success: function(response) {
                if (response.books && response.books.length > 0) {
                    appendBooks(response.books);
                    loading = false;
                } else {
                    hasMore = false;
                }
            },
            error: function() {
                loading = false;
                page--;
            }
        });
    }

    function appendBooks(books) {
        const grid = $('.ebook-grid');

        books.forEach(function(book) {
            const card = createBookCard(book);
            grid.append(card);
        });
    }

    function createBookCard(book) {
        return `
            <div class="ebook-card" onclick="location.href='book.php?id=${book.id}'">
                <div class="ebook-cover">
                    ${book.title}
                    <span class="skill-badge">${book.skill_level_name}</span>
                </div>
                <div class="ebook-info">
                    <span class="category-badge">
                        <span class="nav-icon">${book.category_icon}</span>
                        ${book.category_name}
                    </span>
                    <h3 class="ebook-title">${book.title}</h3>
                    <p class="ebook-author">by ${book.author_name}</p>
                    <div class="ebook-rating">
                        <span class="stars">${displayStars(book.avg_rating)}</span>
                        <span class="rating-count">(${book.review_count})</span>
                    </div>
                    <div class="ebook-footer">
                        <div>
                            ${book.discount_price ? `<span class="original-price">${formatPrice(book.price)}</span>` : ''}
                            <span class="price">${formatPrice(book.discount_price || book.price)}</span>
                        </div>
                        <button class="btn btn-cart btn-add-cart" data-id="${book.id}" onclick="event.stopPropagation()">
                            🛒 담기
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    // 코드 미리보기 토글
    $('.toggle-preview').on('click', function() {
        $(this).next('.code-preview').slideToggle();
        const icon = $(this).find('.toggle-icon');
        icon.text(icon.text() === '▼' ? '▲' : '▼');
    });

    // 탭 전환
    $('.tab-button').on('click', function() {
        const target = $(this).data('tab');

        $('.tab-button').removeClass('active');
        $(this).addClass('active');

        $('.tab-content').removeClass('active');
        $('#' + target).addClass('active');
    });

    // 이미지 레이지 로딩
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img.lazy').forEach(function(img) {
            imageObserver.observe(img);
        });
    }

    // 장바구니 아이템 삭제
    $('.remove-cart-item').on('click', function() {
        const cartId = $(this).data('cart-id');
        const row = $(this).closest('tr');

        $.ajax({
            url: 'php/cart_remove.php',
            type: 'POST',
            data: { cart_id: cartId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    row.fadeOut(300, function() {
                        $(this).remove();
                        updateCartTotal();
                    });
                    showNotification('삭제되었습니다', 'success');
                }
            }
        });
    });

    // 장바구니 합계 업데이트
    function updateCartTotal() {
        let total = 0;
        $('.cart-item').each(function() {
            const price = parseFloat($(this).data('price'));
            total += price;
        });
        $('.cart-total').text(formatPrice(total));
    }

    // 결제하기
    $('#checkout-btn').on('click', function() {
        const items = [];
        $('.cart-item').each(function() {
            items.push({
                ebook_id: $(this).data('ebook-id'),
                price: $(this).data('price')
            });
        });

        if (items.length === 0) {
            showNotification('장바구니가 비어있습니다', 'error');
            return;
        }

        $.ajax({
            url: 'php/checkout.php',
            type: 'POST',
            data: { items: items },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.href = 'payment.php?order_id=' + response.order_id;
                } else {
                    showNotification(response.message, 'error');
                }
            }
        });
    });

    // 페이지 로드 시 장바구니 개수 업데이트
    updateCartCount();

    // 다크모드 토글 (선택사항)
    $('#dark-mode-toggle').on('click', function() {
        $('body').toggleClass('dark-mode');
        const isDark = $('body').hasClass('dark-mode');
        localStorage.setItem('darkMode', isDark);
    });

    // 다크모드 설정 복원
    if (localStorage.getItem('darkMode') === 'true') {
        $('body').addClass('dark-mode');
    }
});

// 애니메이션 추가
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
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
