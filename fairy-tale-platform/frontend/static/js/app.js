// Common JavaScript functions for Fairy Tale Platform

// Check if user is authenticated
function checkAuth() {
    const token = localStorage.getItem('token');
    const user = JSON.parse(localStorage.getItem('user') || 'null');

    if (token && user) {
        $('#auth-buttons').hide();
        $('#user-menu').show();
        $('#username').text(user.username);

        if (user.user_type === 'author') {
            $('#dashboard-link').attr('href', '/author/dashboard');
        } else {
            $('#dashboard-link').attr('href', '/reader/dashboard');
        }
    } else {
        $('#auth-buttons').show();
        $('#user-menu').hide();
    }
}

// Require authentication and user type
function requireAuth(requiredType) {
    const token = localStorage.getItem('token');
    const user = JSON.parse(localStorage.getItem('user') || 'null');

    if (!token || !user) {
        alert('로그인이 필요합니다.');
        window.location.href = '/login';
        return false;
    }

    if (requiredType && user.user_type !== requiredType) {
        alert('접근 권한이 없습니다.');
        window.location.href = '/';
        return false;
    }

    // Update username display
    $('#username').text(user.username);

    return true;
}

// Logout function
$(document).on('click', '#logout-btn', function(e) {
    e.preventDefault();
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = '/';
});

// Format number with comma separator
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Format date to Korean format
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('ko-KR');
}

// AJAX error handler
function handleAjaxError(error) {
    if (error.status === 401) {
        alert('인증이 만료되었습니다. 다시 로그인해주세요.');
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = '/login';
    } else if (error.responseJSON && error.responseJSON.detail) {
        return error.responseJSON.detail;
    } else {
        return '오류가 발생했습니다. 다시 시도해주세요.';
    }
}

// Setup AJAX defaults
$.ajaxSetup({
    beforeSend: function(xhr) {
        const token = localStorage.getItem('token');
        if (token) {
            xhr.setRequestHeader('Authorization', 'Bearer ' + token);
        }
    },
    error: function(xhr, status, error) {
        if (xhr.status === 401) {
            handleAjaxError(xhr);
        }
    }
});
