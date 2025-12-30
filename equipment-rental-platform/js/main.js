/**
 * 고가 장비 대여 플랫폼 - 공통 JavaScript
 */

const API_BASE_URL = 'api/';

// API 호출 헬퍼 함수
const api = {
    get: async function(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = `${API_BASE_URL}${endpoint}${queryString ? '?' + queryString : ''}`;

        try {
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            console.error('API GET Error:', error);
            throw error;
        }
    },

    post: async function(endpoint, data = {}) {
        try {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error('API POST Error:', error);
            throw error;
        }
    },

    put: async function(endpoint, data = {}) {
        try {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error('API PUT Error:', error);
            throw error;
        }
    },

    delete: async function(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = `${API_BASE_URL}${endpoint}${queryString ? '?' + queryString : ''}`;

        try {
            const response = await fetch(url, {
                method: 'DELETE',
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            console.error('API DELETE Error:', error);
            throw error;
        }
    },

    uploadFile: async function(endpoint, formData) {
        try {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                method: 'POST',
                credentials: 'include',
                body: formData
            });
            return await response.json();
        } catch (error) {
            console.error('API Upload Error:', error);
            throw error;
        }
    }
};

// 현재 로그인 사용자 정보
let currentUser = null;

// 인증 확인
async function checkAuth() {
    try {
        const result = await api.get('auth.php?action=check');
        if (result.success) {
            currentUser = result.data;
            return true;
        }
    } catch (error) {
        console.log('Not authenticated');
    }
    currentUser = null;
    return false;
}

// 로그아웃
async function logout() {
    try {
        await api.get('auth.php?action=logout');
        currentUser = null;
        showAlert('로그아웃되었습니다.', 'success');
        setTimeout(() => {
            window.location.href = '../index.html';
        }, 1000);
    } catch (error) {
        console.error('Logout error:', error);
    }
}

// 사용자 메뉴 업데이트
async function updateUserMenu() {
    const isAuthenticated = await checkAuth();
    const userMenuEl = $('#user-menu');

    if (isAuthenticated && currentUser) {
        const dashboardUrl = getDashboardUrl(currentUser.user_type);
        userMenuEl.html(`
            <span style="margin-right: 1rem;">${currentUser.username}님</span>
            <a href="${dashboardUrl}" class="btn btn-sm btn-primary">대시보드</a>
            <button onclick="logout()" class="btn btn-sm btn-outline">로그아웃</button>
        `);
    } else {
        userMenuEl.html(`
            <a href="pages/login.html" class="btn btn-sm btn-outline">로그인</a>
            <a href="pages/register.html" class="btn btn-sm btn-primary">회원가입</a>
        `);
    }
}

// 사용자 타입에 따른 대시보드 URL 반환
function getDashboardUrl(userType) {
    const basePath = window.location.pathname.includes('/pages/') ? '' : 'pages/';

    switch (userType) {
        case 'admin':
            return `${basePath}admin-dashboard.html`;
        case 'supplier':
        case 'both':
            return `${basePath}supplier-dashboard.html`;
        case 'renter':
            return `${basePath}renter-dashboard.html`;
        default:
            return `${basePath}renter-dashboard.html`;
    }
}

// 인증 필수 페이지 체크
async function requireAuth() {
    const isAuthenticated = await checkAuth();
    if (!isAuthenticated) {
        showAlert('로그인이 필요합니다.', 'error');
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 1500);
        return false;
    }
    return true;
}

// 관리자 권한 체크
async function requireAdmin() {
    const isAuthenticated = await checkAuth();
    if (!isAuthenticated || currentUser.user_type !== 'admin') {
        showAlert('관리자 권한이 필요합니다.', 'error');
        setTimeout(() => {
            window.location.href = '../index.html';
        }, 1500);
        return false;
    }
    return true;
}

// 알림 표시
function showAlert(message, type = 'info') {
    const alertClass = `alert-${type === 'error' ? 'error' : type}`;
    const alertHtml = `
        <div class="alert ${alertClass}" style="position: fixed; top: 20px; right: 20px; z-index: 10000; min-width: 300px; animation: slideIn 0.3s;">
            ${message}
        </div>
    `;

    const $alert = $(alertHtml);
    $('body').append($alert);

    setTimeout(() => {
        $alert.fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}

// 날짜 포맷팅
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('ko-KR');
}

// 날짜시간 포맷팅
function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('ko-KR');
}

// 금액 포맷팅
function formatCurrency(amount) {
    if (!amount && amount !== 0) return '-';
    return new Intl.NumberFormat('ko-KR', {
        style: 'currency',
        currency: 'KRW'
    }).format(amount);
}

// 숫자 포맷팅 (콤마 추가)
function formatNumber(number) {
    if (!number && number !== 0) return '-';
    return new Intl.NumberFormat('ko-KR').format(number);
}

// 상태 뱃지 생성
function getStatusBadge(status, type = 'equipment') {
    const statusConfig = {
        equipment: {
            available: { class: 'badge-success', text: '대여가능' },
            reserved: { class: 'badge-warning', text: '예약됨' },
            in_use: { class: 'badge-primary', text: '대여중' },
            returned: { class: 'badge-gray', text: '반납됨' },
            inspecting: { class: 'badge-warning', text: '검수중' },
            maintenance: { class: 'badge-warning', text: '정비중' },
            unavailable: { class: 'badge-danger', text: '대여불가' }
        },
        rental: {
            pending: { class: 'badge-warning', text: '승인대기' },
            approved: { class: 'badge-primary', text: '승인됨' },
            rejected: { class: 'badge-danger', text: '거절됨' },
            active: { class: 'badge-success', text: '대여중' },
            completed: { class: 'badge-gray', text: '완료' },
            cancelled: { class: 'badge-danger', text: '취소됨' },
            disputed: { class: 'badge-danger', text: '분쟁중' }
        },
        payment: {
            unpaid: { class: 'badge-warning', text: '미결제' },
            paid: { class: 'badge-success', text: '결제완료' },
            refunded: { class: 'badge-gray', text: '환불됨' },
            partially_refunded: { class: 'badge-warning', text: '부분환불' }
        },
        settlement: {
            pending: { class: 'badge-warning', text: '대기중' },
            processing: { class: 'badge-primary', text: '처리중' },
            completed: { class: 'badge-success', text: '완료' },
            failed: { class: 'badge-danger', text: '실패' }
        }
    };

    const config = statusConfig[type]?.[status] || { class: 'badge-gray', text: status };
    return `<span class="badge ${config.class}">${config.text}</span>`;
}

// 등급 뱃지 생성
function getGradeBadge(grade) {
    const gradeConfig = {
        'S': { class: 'badge-success', text: 'S급 (최상)' },
        'A': { class: 'badge-primary', text: 'A급 (상)' },
        'B': { class: 'badge-warning', text: 'B급 (중)' },
        'C': { class: 'badge-gray', text: 'C급 (하)' },
        'damaged': { class: 'badge-danger', text: '파손' }
    };

    const config = gradeConfig[grade] || { class: 'badge-gray', text: grade };
    return `<span class="badge ${config.class}">${config.text}</span>`;
}

// 별점 표시
function renderStars(rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    let stars = '';

    for (let i = 0; i < fullStars; i++) {
        stars += '⭐';
    }
    if (hasHalfStar) {
        stars += '⭐'; // 반별은 간단히 전체 별로 표시
    }

    return `<span title="${rating}점">${stars} (${rating.toFixed(1)})</span>`;
}

// 이미지 경로 생성
function getImageUrl(imagePath) {
    if (!imagePath) return 'https://via.placeholder.com/400x300?text=No+Image';
    if (imagePath.startsWith('http')) return imagePath;
    const basePath = window.location.pathname.includes('/pages/') ? '../' : '';
    return `${basePath}uploads/${imagePath}`;
}

// 모달 열기
function openModal(modalId) {
    $(`#${modalId}`).addClass('active');
}

// 모달 닫기
function closeModal(modalId) {
    $(`#${modalId}`).removeClass('active');
}

// 모달 외부 클릭 시 닫기
$(document).on('click', '.modal-overlay', function(e) {
    if ($(e.target).hasClass('modal-overlay')) {
        $(this).removeClass('active');
    }
});

// 확인 대화상자
function confirm(message, callback) {
    if (window.confirm(message)) {
        callback();
    }
}

// 폼 검증
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const inputs = form.querySelectorAll('[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
        } else {
            input.classList.remove('error');
        }
    });

    return isValid;
}

// 이메일 검증
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// 전화번호 포맷팅
function formatPhoneNumber(phone) {
    if (!phone) return '-';
    return phone.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
}

// 로딩 표시
function showLoading(buttonId) {
    const $button = $(`#${buttonId}`);
    $button.prop('disabled', true);
    $button.data('original-text', $button.html());
    $button.html('<span class="loading"></span> 처리중...');
}

// 로딩 숨기기
function hideLoading(buttonId) {
    const $button = $(`#${buttonId}`);
    $button.prop('disabled', false);
    $button.html($button.data('original-text'));
}

// 페이지네이션 렌더링
function renderPagination(pagination, onPageClick) {
    if (!pagination || pagination.total_pages <= 1) return '';

    let html = '<div class="pagination">';

    // 이전 버튼
    if (pagination.page > 1) {
        html += `<button class="pagination-btn" onclick="${onPageClick}(${pagination.page - 1})">이전</button>`;
    }

    // 페이지 번호
    const maxPages = 5;
    let startPage = Math.max(1, pagination.page - Math.floor(maxPages / 2));
    let endPage = Math.min(pagination.total_pages, startPage + maxPages - 1);

    if (endPage - startPage < maxPages - 1) {
        startPage = Math.max(1, endPage - maxPages + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === pagination.page ? 'active' : '';
        html += `<button class="pagination-btn ${activeClass}" onclick="${onPageClick}(${i})">${i}</button>`;
    }

    // 다음 버튼
    if (pagination.page < pagination.total_pages) {
        html += `<button class="pagination-btn" onclick="${onPageClick}(${pagination.page + 1})">다음</button>`;
    }

    html += '</div>';
    return html;
}

// URL 파라미터 가져오기
function getUrlParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

// URL 파라미터 설정
function setUrlParam(param, value) {
    const url = new URL(window.location);
    url.searchParams.set(param, value);
    window.history.pushState({}, '', url);
}

// 날짜 차이 계산 (일 단위)
function getDaysDiff(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays + 1; // 당일 포함
}

// 디바운스 함수 (검색 입력 최적화)
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

// CSS 애니메이션 추가
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

    .error {
        border-color: var(--danger-color) !important;
    }
`;
document.head.appendChild(style);

// 전역 에러 핸들러
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
});

// jQuery 준비 완료
$(document).ready(function() {
    // ESC 키로 모달 닫기
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.modal-overlay.active').removeClass('active');
        }
    });
});
