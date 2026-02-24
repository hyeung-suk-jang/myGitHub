/**
 * jqGrid 게시판 (로컬 실행용)
 * - 샘플 데이터 20건
 * - 목록/검색/글쓰기/수정/삭제/상세보기
 */
$(function () {

    /* ====================================================
       샘플 데이터
    ==================================================== */
    var rawData = [
        { id: 20, category: '공지',   title: '2024년 상반기 서비스 이용약관 변경 안내',         author: '관리자',   date: '2024-06-03', views: 3842, hasFile: true,  content: '안녕하세요.\n2024년 6월 15일부터 서비스 이용약관이 일부 변경됩니다.\n주요 변경 사항은 아래와 같습니다.\n\n1. 개인정보 수집 항목 변경\n2. 서비스 이용 제한 기준 명확화\n3. 분쟁 해결 절차 개선\n\n변경된 약관은 적용일 이전에 별도 공지될 예정이며, 계속 서비스를 이용하시면 변경된 약관에 동의한 것으로 간주합니다.\n\n궁금하신 점은 고객센터(1588-0000)로 문의해 주세요.' },
        { id: 19, category: '공지',   title: '정기 시스템 점검 안내 (6/10 02:00~06:00)',       author: '관리자',   date: '2024-06-01', views: 1527, hasFile: false, content: '안정적인 서비스 제공을 위해 정기 시스템 점검을 실시합니다.\n\n■ 점검 일시: 2024년 6월 10일 (월) 02:00 ~ 06:00\n■ 점검 내용: 서버 인프라 업그레이드 및 보안 패치 적용\n■ 영향 서비스: 전체 서비스 이용 불가\n\n점검 시간 동안 불편을 드려 대단히 죄송합니다.\n이용에 참고하시기 바랍니다.' },
        { id: 18, category: '일반',   title: '신규 기능 업데이트 - 대시보드 개편',              author: '개발팀',   date: '2024-05-28', views: 892,  hasFile: true,  content: '안녕하세요, 개발팀입니다.\n\n이번 업데이트에서는 사용자 대시보드가 전면 개편되었습니다.\n\n주요 변경 사항:\n① 위젯 자유 배치 기능 추가\n② 실시간 통계 차트 제공\n③ 다크모드 지원\n④ 모바일 최적화 레이아웃\n\n불편사항이나 개선 의견이 있으시면 피드백 기능을 이용해 주세요.' },
        { id: 17, category: '공지',   title: '개인정보 처리방침 개정 안내 (v3.2)',              author: '관리자',   date: '2024-05-20', views: 2103, hasFile: false, content: '개인정보 보호법 개정에 따라 개인정보 처리방침이 아래와 같이 개정됩니다.\n\n■ 시행일: 2024년 6월 1일\n■ 주요 변경 내용\n  - 개인정보 보유기간 단축 (5년 → 3년)\n  - 개인정보 국외 이전 조항 신설\n  - 가명정보 처리 근거 명확화\n\n자세한 내용은 첨부파일을 확인해 주세요.' },
        { id: 16, category: '이벤트', title: '봄맞이 이벤트 당첨자 발표',                       author: '이벤트팀', date: '2024-05-15', views: 4561, hasFile: true,  content: '안녕하세요!\n봄맞이 경품 이벤트에 참여해 주신 모든 분들께 진심으로 감사드립니다.\n\n■ 1등 (최신 스마트폰): 홍**님\n■ 2등 (태블릿PC): 김**님, 이**님\n■ 3등 (상품권 5만원): 박**님 외 7명\n\n당첨자분들께는 개별 연락을 드릴 예정입니다.\n경품은 2024년 5월 25일까지 발송됩니다.' },
        { id: 15, category: '공지',   title: '고객센터 운영시간 변경 안내',                      author: '고객지원팀', date: '2024-05-10', views: 731,  hasFile: false, content: '고객 여러분의 더 나은 서비스 경험을 위해 고객센터 운영시간이 변경됩니다.\n\n■ 변경 전: 평일 09:00 ~ 18:00\n■ 변경 후: 평일 08:00 ~ 20:00, 토요일 09:00 ~ 14:00\n\n■ 변경 일자: 2024년 6월 1일(토)부터\n\n더욱 넓은 시간대에 고객 여러분의 문의를 받아드릴 수 있게 되어 기쁩니다.' },
        { id: 14, category: '일반',   title: '모바일 앱 v2.5.0 업데이트 출시',                  author: '개발팀',   date: '2024-05-05', views: 1289, hasFile: false, content: '모바일 앱 v2.5.0이 출시되었습니다.\n\n■ iOS App Store / Google Play에서 업데이트하세요.\n\n주요 변경사항:\n• 성능 최적화 (앱 실행 속도 40% 향상)\n• 지문/안면 인식 로그인 지원\n• 오프라인 모드 지원\n• 버그 수정 다수\n\n업데이트 후 불편사항은 앱 내 피드백으로 알려주세요.' },
        { id: 13, category: '공지',   title: '보안 패치 적용 완료 안내',                         author: '보안팀',   date: '2024-04-29', views: 654,  hasFile: false, content: '최근 발견된 보안 취약점에 대한 패치가 성공적으로 완료되었습니다.\n\n■ 패치 완료 일시: 2024-04-29 03:30\n■ 패치 내용: CVE-2024-1234 취약점 대응\n\n이용자 여러분의 정보 보안을 위해 최선을 다하겠습니다.\n정기적인 비밀번호 변경을 권장드립니다.' },
        { id: 12, category: '일반',   title: '서비스 장애 복구 완료 안내 (4/20)',                author: '운영팀',   date: '2024-04-20', views: 2340, hasFile: false, content: '안녕하세요.\n\n2024년 4월 20일 13:45 ~ 15:30 사이에 발생한 서비스 장애가 복구 완료되었음을 알려드립니다.\n\n■ 장애 원인: 데이터베이스 연결 풀 고갈\n■ 영향 범위: 로그인, 데이터 조회 기능\n■ 조치 사항: DB 연결 설정 최적화 및 서버 재시작\n\n이용에 불편을 드려 진심으로 사과드립니다.' },
        { id: 11, category: '이벤트', title: '신규 회원 가입 혜택 - 첫 달 50% 할인',            author: '마케팅팀', date: '2024-04-15', views: 5823, hasFile: true,  content: '새로운 회원 여러분을 환영합니다!\n\n■ 혜택 내용: 가입 후 첫 달 이용요금 50% 할인\n■ 적용 기간: 가입일로부터 30일\n■ 대상: 2024년 4월 15일 이후 신규 가입 회원\n\n지금 바로 가입하고 특별 혜택을 누려보세요!\n친구 초대 시 추가 포인트도 드립니다.' },
        { id: 10, category: '일반',   title: '포인트 정책 변경 안내 (5월 1일 적용)',             author: '관리자',   date: '2024-04-10', views: 1876, hasFile: false, content: '포인트 정책이 아래와 같이 변경됩니다.\n\n■ 변경 전\n  - 구매금액의 1% 적립\n  - 유효기간: 적립일로부터 1년\n\n■ 변경 후\n  - 구매금액의 1.5% 적립\n  - 유효기간: 적립일로부터 2년\n  - 등급별 추가 적립 혜택 신설\n\n더 많은 혜택으로 보답하겠습니다.' },
        { id: 9,  category: '공지',   title: '계정 보안 강화 - 2단계 인증 권장',                author: '보안팀',   date: '2024-04-05', views: 987,  hasFile: false, content: '이용자 계정 보안 강화를 위해 2단계 인증(OTP) 설정을 강력히 권장드립니다.\n\n■ 2단계 인증 설정 방법\n  1. 마이페이지 → 보안 설정\n  2. 2단계 인증 활성화\n  3. 인증 앱 연동 (Google Authenticator 등)\n\n2단계 인증 설정 시 보안 포인트 500점이 지급됩니다.' },
        { id: 8,  category: '일반',   title: '파트너사 업무 협약(MOU) 체결 안내',               author: '경영지원팀', date: '2024-03-28', views: 432,  hasFile: true,  content: '안녕하세요.\n\n(주)ABC테크놀로지와 업무 협약을 체결하였음을 알려드립니다.\n\n■ 협약 일자: 2024년 3월 28일\n■ 협약 내용: 기술 개발 협력 및 공동 마케팅\n■ 기대 효과: 서비스 품질 향상 및 신규 기능 개발 가속화\n\n더 나은 서비스로 보답하겠습니다.' },
        { id: 7,  category: '이벤트', title: '봄 시즌 프로모션 - 최대 30% 할인',                author: '마케팅팀', date: '2024-03-20', views: 6742, hasFile: false, content: '따뜻한 봄을 맞이하여 특별 프로모션을 진행합니다!\n\n■ 기간: 2024년 3월 20일 ~ 4월 15일\n■ 대상: 전체 유료 구독 상품\n■ 할인율: 최대 30%\n\n이번 기회를 놓치지 마세요!\n자세한 내용은 이벤트 페이지를 확인해 주세요.' },
        { id: 6,  category: 'FAQ',    title: 'FAQ: 비밀번호 변경은 어떻게 하나요?',              author: '고객지원팀', date: '2024-03-15', views: 3102, hasFile: false, content: '비밀번호 변경 방법을 안내드립니다.\n\nQ. 비밀번호를 변경하고 싶습니다.\n\nA. 아래 절차를 따라주세요.\n\n[PC]\n1. 우측 상단 프로필 클릭\n2. 내 정보 관리 → 보안 설정\n3. 비밀번호 변경 클릭\n4. 현재 비밀번호 입력 후 새 비밀번호 설정\n\n[모바일]\n1. 메뉴 → 마이페이지\n2. 설정 → 계정 보안\n3. 비밀번호 변경\n\n비밀번호는 영문+숫자+특수문자 조합 8자 이상으로 설정해 주세요.' },
        { id: 5,  category: 'FAQ',    title: 'FAQ: 환불은 어떤 경우에 가능한가요?',              author: '고객지원팀', date: '2024-03-10', views: 4451, hasFile: false, content: '환불 정책을 안내드립니다.\n\nQ. 환불 가능한 경우는 어떤 경우인가요?\n\nA. 아래 경우에 환불이 가능합니다.\n\n■ 전액 환불\n  - 결제 후 7일 이내, 서비스 미사용 시\n  - 당사 귀책 사유로 인한 서비스 장애\n\n■ 부분 환불\n  - 월정액 상품의 경우 미사용 일수 계산 환불\n  - 포인트 사용 시 포인트 우선 차감\n\n환불 신청은 고객센터 또는 마이페이지에서 가능합니다.' },
        { id: 4,  category: 'FAQ',    title: 'FAQ: 멤버십 등급 기준이 어떻게 되나요?',           author: '고객지원팀', date: '2024-03-05', views: 2867, hasFile: false, content: '멤버십 등급 기준을 안내드립니다.\n\n■ BRONZE (기본)\n  - 가입 즉시 적용\n  - 기본 포인트 적립 1%\n\n■ SILVER\n  - 최근 6개월 누적 결제 30만원 이상\n  - 포인트 적립 1.5%\n  - 월 1회 무료 배송 쿠폰\n\n■ GOLD\n  - 최근 6개월 누적 결제 100만원 이상\n  - 포인트 적립 2%\n  - 전용 고객센터 이용 가능\n\n■ VIP\n  - 최근 6개월 누적 결제 300만원 이상\n  - 포인트 적립 3%\n  - 전담 매니저 배정' },
        { id: 3,  category: '일반',   title: '2024년 1분기 서비스 업데이트 로드맵 공개',         author: '기획팀',   date: '2024-02-20', views: 1543, hasFile: true,  content: '2024년 1분기 업데이트 로드맵을 공개합니다.\n\n■ 1월: 모바일 앱 리뉴얼\n■ 2월: AI 추천 기능 도입\n■ 3월: 실시간 협업 도구 출시\n■ 4월: 글로벌 서비스 확장 (영어, 일어)\n\n각 업데이트에 대한 세부 일정과 기능은 추후 별도 공지드리겠습니다.\n\n기대해 주세요!' },
        { id: 2,  category: '공지',   title: '서비스 런칭 1주년 기념 감사 인사',                 author: '대표이사',  date: '2024-02-10', views: 8910, hasFile: false, content: '안녕하세요, 대표이사 입니다.\n\n어느덧 서비스 런칭 1주년을 맞이하게 되었습니다.\n\n지난 1년 동안 저희 서비스를 사랑해 주신 모든 이용자 여러분께 진심으로 감사드립니다.\n\n내년에도 더 좋은 서비스, 더 많은 기능으로 여러분 곁에 있겠습니다.\n\n감사합니다.' },
        { id: 1,  category: '공지',   title: '서비스 정식 오픈 안내 및 이용 가이드',             author: '관리자',   date: '2024-02-01', views: 12034, hasFile: true, content: '안녕하세요!\n\n드디어 서비스가 정식 오픈하였습니다.\n\n■ 주요 서비스\n  - 게시판 / 공지사항\n  - 개인 대시보드\n  - 실시간 알림\n  - 파일 관리\n\n■ 이용 가이드\n  첨부파일의 PDF 가이드를 참고해 주세요.\n\n서비스 이용 중 문의사항은 고객센터로 연락 주세요.\n\n감사합니다.' }
    ];

    /* 행 번호는 id 그대로 사용, rowid를 키로 관리 */
    var allData    = rawData.slice();   // 전체 원본
    var dispData   = allData.slice();   // 현재 표시 중인 데이터
    var nextId     = allData.length + 1;
    var selectedId = null;              // 선택된 행 id

    /* ====================================================
       그리드 초기화
    ==================================================== */
    function initGrid(data) {
        var $grid = $('#board-grid');

        if ($grid.jqGrid !== undefined && $grid[0].grid) {
            $grid.jqGrid('GridDestroy');
        }

        $grid.jqGrid({
            data: data,
            datatype: 'local',
            colModel: [
                {
                    name: 'id', label: '번호', width: 65, align: 'center',
                    sorttype: 'int',
                    formatter: function (val) {
                        return '<span style="color:#555;">' + val + '</span>';
                    }
                },
                {
                    name: 'category', label: '분류', width: 72, align: 'center',
                    formatter: function (val) {
                        var cls = { '공지': 'badge-notice', '일반': 'badge-general', 'FAQ': 'badge-faq', '이벤트': 'badge-event' };
                        return '<span class="grid-badge ' + (cls[val] || 'badge-general') + '">' + val + '</span>';
                    }
                },
                {
                    name: 'title', label: '제목', width: 380, align: 'left',
                    formatter: function (val, opts, row) {
                        var attach = row.hasFile ? ' <span class="attach-icon" title="첨부파일">&#128206;</span>' : '';
                        return '<a href="#" class="title-link" data-id="' + row.id + '">' + escapeHtml(val) + '</a>' + attach;
                    }
                },
                { name: 'author', label: '작성자', width: 90, align: 'center' },
                { name: 'date',   label: '등록일', width: 100, align: 'center' },
                {
                    name: 'views', label: '조회수', width: 80, align: 'center',
                    sorttype: 'int',
                    formatter: function (val) {
                        return Number(val).toLocaleString();
                    }
                },
                {
                    name: 'hasFile', label: '첨부', width: 55, align: 'center',
                    formatter: function (val) {
                        return val ? '<span class="attach-icon" title="첨부파일 있음">&#128206;</span>' : '';
                    }
                }
            ],
            viewrecords : true,
            sortname    : 'id',
            sortorder   : 'desc',
            autowidth   : true,
            height      : 'auto',
            rowNum      : parseInt($('#rows-per-page').val(), 10) || 10,
            rowList     : [10, 20, 30],
            pager       : '#board-pager',
            caption     : '게시판',
            gridview    : true,
            idPrefix    : 'gr_',

            onSelectRow: function (rowid) {
                // rowid 에서 순수 id 추출 (idPrefix='gr_' 이므로 그냥 rowid를 키로)
                selectedId = rowid;
            },

            onCellSelect: function (rowid, iCol) {
                // 제목 셀 클릭 → 상세보기 (iCol==2)
                if (iCol === 2) {
                    viewPost(rowid);
                }
            },

            ondblClickRow: function (rowid) {
                viewPost(rowid);
            },

            loadComplete: function () {
                updateTotalCount();
            }
        });

        // 행 번호가 없는 경우 "데이터 없음" 표시
        updateTotalCount();
    }

    /* ====================================================
       총 건수 업데이트
    ==================================================== */
    function updateTotalCount() {
        $('#total-num').text(dispData.length);
    }

    /* ====================================================
       특수문자 이스케이프
    ==================================================== */
    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* ====================================================
       id 로 데이터 찾기
    ==================================================== */
    function findById(id) {
        for (var i = 0; i < allData.length; i++) {
            if (String(allData[i].id) === String(id)) return allData[i];
        }
        return null;
    }

    /* ====================================================
       상세보기 모달
    ==================================================== */
    function viewPost(rowid) {
        // jqGrid rowid 는 'gr_숫자' 형태 (idPrefix)
        var pureId = rowid.replace(/^gr_/, '');
        var row = findById(pureId);
        if (!row) { return; }

        // 조회수 증가
        row.views += 1;
        refreshGrid();

        var catClass = row.category;
        $('#v-category').attr('class', 'badge ' + catClass).text(row.category);
        $('#v-title').text(row.title);
        $('#v-author').text(row.author);
        $('#v-date').text(row.date);
        $('#v-views').text(Number(row.views).toLocaleString() + '회');
        $('#v-file').html(row.hasFile ? '<span class="attach-icon">&#128206;</span> 첨부파일_샘플.pdf' : '없음');
        $('#v-content').text(row.content);

        $('#view-modal').dialog({
            title    : '게시글 상세보기',
            modal    : true,
            width    : 680,
            maxHeight: 600,
            resizable: false,
            buttons  : [
                {
                    text : '수정',
                    class: 'ui-button',
                    style: 'background:#5f6368;color:#fff;',
                    click: function () {
                        $(this).dialog('close');
                        openEditForm(pureId);
                    }
                },
                {
                    text : '닫기',
                    class: 'ui-button',
                    style: 'background:#e8eaed;color:#444;',
                    click: function () { $(this).dialog('close'); }
                }
            ]
        });
    }

    /* ====================================================
       그리드 새로고침 (데이터 변경 후 재렌더링)
    ==================================================== */
    function refreshGrid() {
        $('#board-grid').jqGrid('clearGridData');
        for (var i = 0; i < dispData.length; i++) {
            $('#board-grid').jqGrid('addRowData', 'gr_' + dispData[i].id, dispData[i]);
        }
        updateTotalCount();
    }

    /* ====================================================
       글쓰기 폼 열기
    ==================================================== */
    function openAddForm() {
        $('#post-form')[0].reset();
        $('#f-id').val('');
        $('#f-category').val('공지');
        $('#form-modal').dialog({
            title    : '글쓰기',
            modal    : true,
            width    : 620,
            resizable: false,
            buttons  : [
                {
                    text : '저장',
                    class: 'ui-button',
                    style: 'background:#1a73e8;color:#fff;font-weight:600;',
                    click: function () {
                        if (savePost()) { $(this).dialog('close'); }
                    }
                },
                {
                    text : '취소',
                    class: 'ui-button',
                    style: 'background:#e8eaed;color:#444;',
                    click: function () { $(this).dialog('close'); }
                }
            ]
        });
    }

    /* ====================================================
       수정 폼 열기
    ==================================================== */
    function openEditForm(pureId) {
        var row = findById(pureId);
        if (!row) { alert('선택된 게시글이 없습니다.'); return; }

        $('#f-id').val(row.id);
        $('#f-category').val(row.category);
        $('#f-title').val(row.title);
        $('#f-author').val(row.author);
        $('#f-content').val(row.content);
        $('#f-hasfile').prop('checked', row.hasFile);

        $('#form-modal').dialog({
            title    : '게시글 수정',
            modal    : true,
            width    : 620,
            resizable: false,
            buttons  : [
                {
                    text : '저장',
                    class: 'ui-button',
                    style: 'background:#1a73e8;color:#fff;font-weight:600;',
                    click: function () {
                        if (savePost()) { $(this).dialog('close'); }
                    }
                },
                {
                    text : '취소',
                    class: 'ui-button',
                    style: 'background:#e8eaed;color:#444;',
                    click: function () { $(this).dialog('close'); }
                }
            ]
        });
    }

    /* ====================================================
       저장 (신규 / 수정)
    ==================================================== */
    function savePost() {
        var title  = $.trim($('#f-title').val());
        var author = $.trim($('#f-author').val());

        if (!title)  { alert('제목을 입력해 주세요.'); $('#f-title').focus(); return false; }
        if (!author) { alert('작성자를 입력해 주세요.'); $('#f-author').focus(); return false; }

        var existingId = $('#f-id').val();

        if (existingId) {
            // 수정
            var row = findById(existingId);
            if (row) {
                row.category = $('#f-category').val();
                row.title    = title;
                row.author   = author;
                row.content  = $('#f-content').val();
                row.hasFile  = $('#f-hasfile').is(':checked');
            }
        } else {
            // 신규
            var today = new Date();
            var dateStr = today.getFullYear() + '-' +
                String(today.getMonth() + 1).padStart(2, '0') + '-' +
                String(today.getDate()).padStart(2, '0');

            var newRow = {
                id      : nextId,
                category: $('#f-category').val(),
                title   : title,
                author  : author,
                date    : dateStr,
                views   : 0,
                hasFile : $('#f-hasfile').is(':checked'),
                content : $('#f-content').val()
            };
            allData.unshift(newRow);
            nextId++;
        }

        dispData = allData.slice();
        refreshGrid();
        return true;
    }

    /* ====================================================
       삭제
    ==================================================== */
    function deletePost(rowid) {
        var pureId = rowid ? rowid.replace(/^gr_/, '') : null;
        if (!pureId) { alert('삭제할 게시글을 선택해 주세요.'); return; }

        var row = findById(pureId);
        if (!row) { alert('선택된 게시글이 없습니다.'); return; }

        if (!confirm('"' + row.title + '"\n\n위 게시글을 삭제하시겠습니까?')) { return; }

        allData = allData.filter(function (d) { return String(d.id) !== String(pureId); });
        dispData = allData.slice();
        selectedId = null;
        refreshGrid();
    }

    /* ====================================================
       검색
    ==================================================== */
    function doSearch() {
        var type    = $('#search-type').val();
        var keyword = $.trim($('#search-keyword').val()).toLowerCase();

        if (!keyword) {
            dispData = allData.slice();
        } else {
            dispData = allData.filter(function (d) {
                if (type === 'title')   return d.title.toLowerCase().indexOf(keyword) >= 0;
                if (type === 'author')  return d.author.toLowerCase().indexOf(keyword) >= 0;
                if (type === 'content') return d.content.toLowerCase().indexOf(keyword) >= 0;
                if (type === 'all')     return (
                    d.title.toLowerCase().indexOf(keyword) >= 0 ||
                    d.author.toLowerCase().indexOf(keyword) >= 0 ||
                    d.content.toLowerCase().indexOf(keyword) >= 0
                );
                return true;
            });
        }

        $('#board-grid').jqGrid('clearGridData');
        for (var i = 0; i < dispData.length; i++) {
            $('#board-grid').jqGrid('addRowData', 'gr_' + dispData[i].id, dispData[i]);
        }
        updateTotalCount();
        selectedId = null;
    }

    /* ====================================================
       이벤트 바인딩
    ==================================================== */

    // 글쓰기
    $('#btn-add').on('click', function () { openAddForm(); });

    // 수정
    $('#btn-edit').on('click', function () {
        if (!selectedId) { alert('수정할 게시글을 선택해 주세요.'); return; }
        openEditForm(selectedId.replace(/^gr_/, ''));
    });

    // 삭제
    $('#btn-delete').on('click', function () {
        if (!selectedId) { alert('삭제할 게시글을 선택해 주세요.'); return; }
        deletePost(selectedId);
    });

    // 검색 버튼
    $('#btn-search').on('click', function () { doSearch(); });

    // 검색어 입력 후 Enter
    $('#search-keyword').on('keydown', function (e) {
        if (e.key === 'Enter') { doSearch(); }
    });

    // 초기화
    $('#btn-reset').on('click', function () {
        $('#search-keyword').val('');
        $('#search-type').val('title');
        dispData = allData.slice();
        refreshGrid();
        selectedId = null;
    });

    // 목록 수 변경
    $('#rows-per-page').on('change', function () {
        var n = parseInt($(this).val(), 10);
        $('#board-grid').jqGrid('setGridParam', { rowNum: n }).trigger('reloadGrid');
    });

    // 제목 링크 클릭 (이벤트 위임)
    $(document).on('click', '.title-link', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        viewPost('gr_' + id);
    });

    /* ====================================================
       그리드 최초 로드
    ==================================================== */
    initGrid(dispData);

    /* ====================================================
       반응형: 창 크기 변경 시 그리드 너비 재설정
    ==================================================== */
    var resizeTimer;
    $(window).on('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            var $grid = $('#board-grid');
            if ($grid[0] && $grid[0].grid) {
                var newWidth = $('#grid-container').width();
                $grid.jqGrid('setGridWidth', newWidth);
            }
        }, 150);
    });

});
