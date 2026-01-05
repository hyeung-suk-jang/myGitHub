// API 통신 유틸리티

const API_BASE_URL = 'http://localhost:5000/api';

// API 요청 헬퍼 함수
async function apiRequest(endpoint, options = {}) {
    const defaultOptions = {
        credentials: 'include', // 쿠키 포함
        headers: {
            'Content-Type': 'application/json',
        }
    };

    const config = { ...defaultOptions, ...options };

    if (config.body && typeof config.body === 'object') {
        config.body = JSON.stringify(config.body);
    }

    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || '요청 처리 중 오류가 발생했습니다.');
        }

        return data;
    } catch (error) {
        console.error('API 요청 오류:', error);
        throw error;
    }
}

// 인증 API
const AuthAPI = {
    login: (username, password) =>
        apiRequest('/auth/login', {
            method: 'POST',
            body: { username, password }
        }),

    logout: () =>
        apiRequest('/auth/logout', { method: 'POST' }),

    checkAuth: () =>
        apiRequest('/auth/check'),

    register: (userData) =>
        apiRequest('/auth/register', {
            method: 'POST',
            body: userData
        })
};

// 과정 API
const CourseAPI = {
    getAll: () =>
        apiRequest('/courses/'),

    getById: (id) =>
        apiRequest(`/courses/${id}`),

    create: (courseData) =>
        apiRequest('/courses/', {
            method: 'POST',
            body: courseData
        }),

    update: (id, courseData) =>
        apiRequest(`/courses/${id}`, {
            method: 'PUT',
            body: courseData
        }),

    delete: (id) =>
        apiRequest(`/courses/${id}`, { method: 'DELETE' }),

    getClasses: (id) =>
        apiRequest(`/courses/${id}/classes`),

    enroll: (courseId, classId) =>
        apiRequest(`/courses/${courseId}/enroll`, {
            method: 'POST',
            body: { class_id: classId }
        })
};

// 시험 API
const ExamAPI = {
    getAll: () =>
        apiRequest('/exams/'),

    getById: (id) =>
        apiRequest(`/exams/${id}`),

    create: (examData) =>
        apiRequest('/exams/', {
            method: 'POST',
            body: examData
        }),

    start: (id) =>
        apiRequest(`/exams/${id}/start`, { method: 'POST' }),

    submit: (attemptId, answers) =>
        apiRequest(`/exams/attempts/${attemptId}/submit`, {
            method: 'POST',
            body: { answers }
        }),

    getResults: (attemptId) =>
        apiRequest(`/exams/attempts/${attemptId}/results`),

    // 문제 은행
    getQuestions: (params = {}) => {
        const queryString = new URLSearchParams(params).toString();
        return apiRequest(`/exams/questions${queryString ? '?' + queryString : ''}`);
    },

    createQuestion: (questionData) =>
        apiRequest('/exams/questions', {
            method: 'POST',
            body: questionData
        }),

    updateQuestion: (id, questionData) =>
        apiRequest(`/exams/questions/${id}`, {
            method: 'PUT',
            body: questionData
        }),

    deleteQuestion: (id) =>
        apiRequest(`/exams/questions/${id}`, { method: 'DELETE' })
};

// 교재 API
const MaterialAPI = {
    getAll: (params = {}) => {
        const queryString = new URLSearchParams(params).toString();
        return apiRequest(`/materials/${queryString ? '?' + queryString : ''}`);
    },

    getById: (id) =>
        apiRequest(`/materials/${id}`),

    create: (materialData) =>
        apiRequest('/materials/', {
            method: 'POST',
            body: materialData
        }),

    update: (id, materialData) =>
        apiRequest(`/materials/${id}`, {
            method: 'PUT',
            body: materialData
        }),

    delete: (id) =>
        apiRequest(`/materials/${id}`, { method: 'DELETE' }),

    updateProgress: (id, progress, completed) =>
        apiRequest(`/materials/${id}/progress`, {
            method: 'POST',
            body: { progress, completed }
        })
};

// 학생 API
const StudentAPI = {
    getAll: (params = {}) => {
        const queryString = new URLSearchParams(params).toString();
        return apiRequest(`/students/${queryString ? '?' + queryString : ''}`);
    },

    getById: (id) =>
        apiRequest(`/students/${id}`),

    getAttendance: (id) =>
        apiRequest(`/students/${id}/attendance`),

    getCounseling: (id) =>
        apiRequest(`/students/${id}/counseling`),

    addCounseling: (id, noteData) =>
        apiRequest(`/students/${id}/counseling`, {
            method: 'POST',
            body: noteData
        }),

    getDashboard: () =>
        apiRequest('/students/my-dashboard')
};
