/**
 * 다국어 지원 시스템 (i18n - Internationalization)
 * 지원 언어: 한국어(ko), 영어(en), 일본어(ja)
 */

const translations = {
    ko: {
        // 공통
        'app.name': 'EquipRent',
        'app.tagline': '고가 장비를 안전하게 대여하세요',
        'common.login': '로그인',
        'common.logout': '로그아웃',
        'common.register': '회원가입',
        'common.search': '검색',
        'common.submit': '제출',
        'common.cancel': '취소',
        'common.confirm': '확인',
        'common.save': '저장',
        'common.delete': '삭제',
        'common.edit': '수정',
        'common.close': '닫기',
        'common.loading': '로딩중...',
        'common.error': '오류',
        'common.success': '성공',

        // 네비게이션
        'nav.home': '홈',
        'nav.equipment': '장비 검색',
        'nav.myEquipment': '내 장비',
        'nav.rentals': '대여 내역',
        'nav.chat': '채팅',
        'nav.dashboard': '대시보드',

        // 인증
        'auth.email': '이메일',
        'auth.password': '비밀번호',
        'auth.passwordConfirm': '비밀번호 확인',
        'auth.username': '이름',
        'auth.phone': '전화번호',
        'auth.loginSuccess': '로그인되었습니다',
        'auth.logoutSuccess': '로그아웃되었습니다',
        'auth.registerSuccess': '회원가입이 완료되었습니다',
        'auth.loginRequired': '로그인이 필요합니다',

        // 장비
        'equipment.title': '장비',
        'equipment.register': '장비 등록',
        'equipment.name': '장비명',
        'equipment.brand': '브랜드',
        'equipment.model': '모델명',
        'equipment.category': '카테고리',
        'equipment.condition': '상태',
        'equipment.dailyRate': '1일 대여료',
        'equipment.deposit': '보증금',
        'equipment.location': '위치',
        'equipment.description': '설명',
        'equipment.available': '대여가능',
        'equipment.unavailable': '대여불가',

        // 대여
        'rental.request': '대여 신청',
        'rental.approve': '승인',
        'rental.reject': '거절',
        'rental.cancel': '취소',
        'rental.startDate': '시작일',
        'rental.endDate': '종료일',
        'rental.totalDays': '총 일수',
        'rental.status': '상태',

        // 결제
        'payment.title': '결제',
        'payment.method': '결제 수단',
        'payment.totalAmount': '총 결제금액',
        'payment.pay': '결제하기',
        'payment.success': '결제가 완료되었습니다',
        'payment.failed': '결제에 실패했습니다',

        // 채팅
        'chat.title': '채팅',
        'chat.sendMessage': '메시지 전송',
        'chat.inputPlaceholder': '메시지를 입력하세요...',

        // 에러 메시지
        'error.required': '필수 항목입니다',
        'error.invalidEmail': '올바른 이메일 형식이 아닙니다',
        'error.passwordMismatch': '비밀번호가 일치하지 않습니다',
        'error.networkError': '네트워크 오류가 발생했습니다'
    },

    en: {
        // Common
        'app.name': 'EquipRent',
        'app.tagline': 'Rent high-value equipment safely',
        'common.login': 'Login',
        'common.logout': 'Logout',
        'common.register': 'Sign Up',
        'common.search': 'Search',
        'common.submit': 'Submit',
        'common.cancel': 'Cancel',
        'common.confirm': 'Confirm',
        'common.save': 'Save',
        'common.delete': 'Delete',
        'common.edit': 'Edit',
        'common.close': 'Close',
        'common.loading': 'Loading...',
        'common.error': 'Error',
        'common.success': 'Success',

        // Navigation
        'nav.home': 'Home',
        'nav.equipment': 'Browse Equipment',
        'nav.myEquipment': 'My Equipment',
        'nav.rentals': 'My Rentals',
        'nav.chat': 'Chat',
        'nav.dashboard': 'Dashboard',

        // Auth
        'auth.email': 'Email',
        'auth.password': 'Password',
        'auth.passwordConfirm': 'Confirm Password',
        'auth.username': 'Name',
        'auth.phone': 'Phone',
        'auth.loginSuccess': 'Login successful',
        'auth.logoutSuccess': 'Logged out',
        'auth.registerSuccess': 'Registration completed',
        'auth.loginRequired': 'Login required',

        // Equipment
        'equipment.title': 'Equipment',
        'equipment.register': 'Register Equipment',
        'equipment.name': 'Equipment Name',
        'equipment.brand': 'Brand',
        'equipment.model': 'Model',
        'equipment.category': 'Category',
        'equipment.condition': 'Condition',
        'equipment.dailyRate': 'Daily Rate',
        'equipment.deposit': 'Deposit',
        'equipment.location': 'Location',
        'equipment.description': 'Description',
        'equipment.available': 'Available',
        'equipment.unavailable': 'Unavailable',

        // Rental
        'rental.request': 'Request Rental',
        'rental.approve': 'Approve',
        'rental.reject': 'Reject',
        'rental.cancel': 'Cancel',
        'rental.startDate': 'Start Date',
        'rental.endDate': 'End Date',
        'rental.totalDays': 'Total Days',
        'rental.status': 'Status',

        // Payment
        'payment.title': 'Payment',
        'payment.method': 'Payment Method',
        'payment.totalAmount': 'Total Amount',
        'payment.pay': 'Pay Now',
        'payment.success': 'Payment completed',
        'payment.failed': 'Payment failed',

        // Chat
        'chat.title': 'Chat',
        'chat.sendMessage': 'Send Message',
        'chat.inputPlaceholder': 'Type a message...',

        // Error messages
        'error.required': 'This field is required',
        'error.invalidEmail': 'Invalid email format',
        'error.passwordMismatch': 'Passwords do not match',
        'error.networkError': 'Network error occurred'
    },

    ja: {
        // 共通
        'app.name': 'EquipRent',
        'app.tagline': '高価な機器を安全にレンタル',
        'common.login': 'ログイン',
        'common.logout': 'ログアウト',
        'common.register': '新規登録',
        'common.search': '検索',
        'common.submit': '送信',
        'common.cancel': 'キャンセル',
        'common.confirm': '確認',
        'common.save': '保存',
        'common.delete': '削除',
        'common.edit': '編集',
        'common.close': '閉じる',
        'common.loading': '読み込み中...',
        'common.error': 'エラー',
        'common.success': '成功',

        // ナビゲーション
        'nav.home': 'ホーム',
        'nav.equipment': '機器検索',
        'nav.myEquipment': 'マイ機器',
        'nav.rentals': 'レンタル履歴',
        'nav.chat': 'チャット',
        'nav.dashboard': 'ダッシュボード',

        // 認証
        'auth.email': 'メール',
        'auth.password': 'パスワード',
        'auth.passwordConfirm': 'パスワード確認',
        'auth.username': '名前',
        'auth.phone': '電話番号',
        'auth.loginSuccess': 'ログインしました',
        'auth.logoutSuccess': 'ログアウトしました',
        'auth.registerSuccess': '登録が完了しました',
        'auth.loginRequired': 'ログインが必要です',

        // 機器
        'equipment.title': '機器',
        'equipment.register': '機器登録',
        'equipment.name': '機器名',
        'equipment.brand': 'ブランド',
        'equipment.model': 'モデル',
        'equipment.category': 'カテゴリー',
        'equipment.condition': '状態',
        'equipment.dailyRate': '1日レンタル料',
        'equipment.deposit': '保証金',
        'equipment.location': '場所',
        'equipment.description': '説明',
        'equipment.available': 'レンタル可能',
        'equipment.unavailable': 'レンタル不可',

        // レンタル
        'rental.request': 'レンタル申請',
        'rental.approve': '承認',
        'rental.reject': '拒否',
        'rental.cancel': 'キャンセル',
        'rental.startDate': '開始日',
        'rental.endDate': '終了日',
        'rental.totalDays': '合計日数',
        'rental.status': 'ステータス',

        // 決済
        'payment.title': '決済',
        'payment.method': '決済方法',
        'payment.totalAmount': '合計金額',
        'payment.pay': '決済する',
        'payment.success': '決済が完了しました',
        'payment.failed': '決済に失敗しました',

        // チャット
        'chat.title': 'チャット',
        'chat.sendMessage': 'メッセージ送信',
        'chat.inputPlaceholder': 'メッセージを入力...',

        // エラーメッセージ
        'error.required': '必須項目です',
        'error.invalidEmail': '無効なメール形式です',
        'error.passwordMismatch': 'パスワードが一致しません',
        'error.networkError': 'ネットワークエラーが発生しました'
    }
};

// 현재 언어 (로컬스토리지에서 로드 또는 기본값)
let currentLanguage = localStorage.getItem('language') || 'ko';

/**
 * 번역 텍스트 가져오기
 */
function t(key, params = {}) {
    let text = translations[currentLanguage]?.[key] || translations['ko'][key] || key;

    // 파라미터 치환
    Object.keys(params).forEach(param => {
        text = text.replace(`{${param}}`, params[param]);
    });

    return text;
}

/**
 * 언어 변경
 */
function setLanguage(lang) {
    if (!translations[lang]) {
        console.error('Unsupported language:', lang);
        return;
    }

    currentLanguage = lang;
    localStorage.setItem('language', lang);

    // 페이지의 모든 번역 텍스트 업데이트
    translatePage();

    // HTML lang 속성 업데이트
    document.documentElement.lang = lang;

    // 이벤트 발생
    $(document).trigger('languageChanged', lang);
}

/**
 * 현재 언어 가져오기
 */
function getCurrentLanguage() {
    return currentLanguage;
}

/**
 * 페이지의 모든 텍스트 번역
 */
function translatePage() {
    // data-i18n 속성을 가진 모든 요소 번역
    $('[data-i18n]').each(function() {
        const key = $(this).data('i18n');
        const text = t(key);

        if ($(this).is('input, textarea')) {
            // 입력 필드는 placeholder로
            $(this).attr('placeholder', text);
        } else {
            // 일반 요소는 텍스트로
            $(this).text(text);
        }
    });

    // data-i18n-html 속성을 가진 요소 번역 (HTML 포함)
    $('[data-i18n-html]').each(function() {
        const key = $(this).data('i18n-html');
        $(this).html(t(key));
    });

    // data-i18n-title 속성을 가진 요소 번역 (title 속성)
    $('[data-i18n-title]').each(function() {
        const key = $(this).data('i18n-title');
        $(this).attr('title', t(key));
    });
}

/**
 * 언어 선택 UI 생성
 */
function createLanguageSelector() {
    const selector = `
        <select id="languageSelector" class="form-control" style="width: auto; display: inline-block;">
            <option value="ko" ${currentLanguage === 'ko' ? 'selected' : ''}>한국어</option>
            <option value="en" ${currentLanguage === 'en' ? 'selected' : ''}>English</option>
            <option value="ja" ${currentLanguage === 'ja' ? 'selected' : ''}>日本語</option>
        </select>
    `;

    return selector;
}

// 페이지 로드 시 자동 번역
$(document).ready(function() {
    translatePage();

    // 언어 선택기 이벤트 바인딩
    $(document).on('change', '#languageSelector', function() {
        setLanguage($(this).val());
    });
});

// 전역으로 노출
window.t = t;
window.setLanguage = setLanguage;
window.getCurrentLanguage = getCurrentLanguage;
window.createLanguageSelector = createLanguageSelector;
