// 유틸리티 함수

// 날짜 포맷팅
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// 날짜 시간 포맷팅
function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day} ${hours}:${minutes}`;
}

// 성공 메시지 표시
function showSuccess(message) {
    showAlert(message, 'success');
}

// 에러 메시지 표시
function showError(message) {
    showAlert(message, 'danger');
}

// 알림 메시지 표시
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.style.animation = 'slideIn 0.3s ease';

    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(alertDiv);
        }, 300);
    }, 3000);
}

// 로딩 표시
function showLoading() {
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'loading-overlay';
    loadingDiv.innerHTML = `
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(0,0,0,0.5); display: flex; align-items: center;
                    justify-content: center; z-index: 9999;">
            <div class="spinner"></div>
        </div>
    `;
    document.body.appendChild(loadingDiv);
}

// 로딩 숨김
function hideLoading() {
    const loadingDiv = document.getElementById('loading-overlay');
    if (loadingDiv) {
        document.body.removeChild(loadingDiv);
    }
}

// 모달 열기
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
    }
}

// 모달 닫기
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
}

// 현재 사용자 정보 가져오기
function getCurrentUser() {
    const userStr = sessionStorage.getItem('currentUser');
    return userStr ? JSON.parse(userStr) : null;
}

// 현재 사용자 정보 저장
function setCurrentUser(user) {
    sessionStorage.setItem('currentUser', JSON.stringify(user));
}

// 현재 사용자 정보 삭제
function clearCurrentUser() {
    sessionStorage.removeItem('currentUser');
}

// 권한 확인
function hasRole(role) {
    const user = getCurrentUser();
    return user && user.role === role;
}

// 배지 색상 가져오기
function getBadgeClass(status) {
    const badgeMap = {
        'active': 'badge-success',
        'completed': 'badge-primary',
        'pending': 'badge-warning',
        'dropped': 'badge-secondary',
        'present': 'badge-success',
        'absent': 'badge-danger',
        'late': 'badge-warning',
        'excused': 'badge-secondary',
        'in_progress': 'badge-warning',
        'graded': 'badge-success'
    };
    return badgeMap[status] || 'badge-secondary';
}

// 난이도 한글 변환
function getDifficultyText(difficulty) {
    const difficultyMap = {
        'easy': '쉬움',
        'medium': '보통',
        'hard': '어려움',
        'beginner': '초급',
        'intermediate': '중급',
        'advanced': '고급'
    };
    return difficultyMap[difficulty] || difficulty;
}

// 문제 유형 한글 변환
function getQuestionTypeText(type) {
    const typeMap = {
        'multiple_choice': '객관식',
        'subjective': '주관식',
        'matching': '선잇기'
    };
    return typeMap[type] || type;
}

// 파일 타입 아이콘 가져오기
function getFileIcon(fileType) {
    const iconMap = {
        'pdf': '📄',
        'video': '🎥',
        'document': '📝',
        'link': '🔗'
    };
    return iconMap[fileType] || '📁';
}

// 페이지 이동
function navigateTo(path) {
    window.location.href = path;
}

// 로그아웃
async function logout() {
    try {
        await AuthAPI.logout();
        clearCurrentUser();
        showSuccess('로그아웃되었습니다.');
        setTimeout(() => {
            navigateTo('login.html');
        }, 1000);
    } catch (error) {
        showError('로그아웃 중 오류가 발생했습니다.');
    }
}

// 인증 확인
async function checkAuth(redirectToLogin = true) {
    try {
        const response = await AuthAPI.checkAuth();
        if (response.success && response.authenticated) {
            setCurrentUser(response.user);
            return response.user;
        } else {
            throw new Error('인증되지 않음');
        }
    } catch (error) {
        clearCurrentUser();
        if (redirectToLogin && !window.location.pathname.includes('login.html')) {
            showError('로그인이 필요합니다.');
            setTimeout(() => {
                navigateTo('login.html');
            }, 1000);
        }
        return null;
    }
}

// 애니메이션 CSS 추가
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

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
