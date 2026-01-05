from flask import Blueprint, request, jsonify, session
from utils.database import execute_query

students_bp = Blueprint('students', __name__, url_prefix='/api/students')

@students_bp.route('/', methods=['GET'])
def get_students():
    """학생 목록 조회 (관리자/강사 전용)"""
    if 'user_id' not in session or session.get('role') not in ['admin', 'instructor', 'system_admin']:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    class_id = request.args.get('class_id')

    if class_id:
        # 특정 반의 학생 목록
        students = execute_query('''
            SELECT u.*, e.enrollment_date, e.status as enrollment_status
            FROM users u
            INNER JOIN enrollments e ON u.id = e.student_id
            WHERE e.class_id = ? AND u.role = 'student'
            ORDER BY u.full_name
        ''', (class_id,), fetch_all=True)
    else:
        # 전체 학생 목록
        students = execute_query('''
            SELECT * FROM users
            WHERE role = 'student' AND is_active = 1
            ORDER BY full_name
        ''', fetch_all=True)

    return jsonify({'success': True, 'students': students})

@students_bp.route('/<int:student_id>', methods=['GET'])
def get_student(student_id):
    """학생 상세 정보 조회"""
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': '로그인이 필요합니다.'}), 401

    # 권한 확인 (본인 또는 관리자/강사)
    role = session.get('role')
    if role == 'student' and session['user_id'] != student_id:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    student = execute_query('SELECT * FROM users WHERE id = ? AND role = "student"',
                           (student_id,), fetch_one=True)

    if not student:
        return jsonify({'success': False, 'message': '학생을 찾을 수 없습니다.'}), 404

    # 수강 중인 과정
    enrollments = execute_query('''
        SELECT e.*, c.class_name, co.course_name, u.full_name as instructor_name
        FROM enrollments e
        INNER JOIN classes c ON e.class_id = c.id
        INNER JOIN courses co ON c.course_id = co.id
        LEFT JOIN users u ON c.instructor_id = u.id
        WHERE e.student_id = ?
        ORDER BY e.enrollment_date DESC
    ''', (student_id,), fetch_all=True)

    student['enrollments'] = enrollments

    # 시험 성적
    exam_results = execute_query('''
        SELECT ea.*, e.exam_name, e.pass_score
        FROM exam_attempts ea
        INNER JOIN exams e ON ea.exam_id = e.id
        WHERE ea.student_id = ? AND ea.status = 'completed'
        ORDER BY ea.submit_time DESC
        LIMIT 10
    ''', (student_id,), fetch_all=True)

    student['exam_results'] = exam_results

    return jsonify({'success': True, 'student': student})

@students_bp.route('/<int:student_id>/attendance', methods=['GET'])
def get_student_attendance(student_id):
    """학생 출결 기록 조회"""
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': '로그인이 필요합니다.'}), 401

    # 권한 확인
    role = session.get('role')
    if role == 'student' and session['user_id'] != student_id:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    attendance = execute_query('''
        SELECT a.*, c.class_name, co.course_name
        FROM attendance a
        INNER JOIN classes c ON a.class_id = c.id
        INNER JOIN courses co ON c.course_id = co.id
        WHERE a.student_id = ?
        ORDER BY a.attendance_date DESC
    ''', (student_id,), fetch_all=True)

    return jsonify({'success': True, 'attendance': attendance})

@students_bp.route('/<int:student_id>/counseling', methods=['GET'])
def get_counseling_notes(student_id):
    """상담 메모 조회"""
    if 'user_id' not in session or session.get('role') not in ['admin', 'instructor', 'system_admin', 'assistant']:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    notes = execute_query('''
        SELECT cn.*, u.full_name as counselor_name
        FROM counseling_notes cn
        INNER JOIN users u ON cn.counselor_id = u.id
        WHERE cn.student_id = ?
        ORDER BY cn.note_date DESC
    ''', (student_id,), fetch_all=True)

    return jsonify({'success': True, 'notes': notes})

@students_bp.route('/<int:student_id>/counseling', methods=['POST'])
def add_counseling_note(student_id):
    """상담 메모 추가"""
    if 'user_id' not in session or session.get('role') not in ['admin', 'instructor', 'system_admin', 'assistant']:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    data = request.get_json()

    note_id = execute_query('''
        INSERT INTO counseling_notes (student_id, counselor_id, note_date, content, category)
        VALUES (?, ?, ?, ?, ?)
    ''', (student_id, session['user_id'], data.get('note_date'), data['content'], data.get('category', '')),
        commit=True
    )

    if note_id:
        return jsonify({'success': True, 'message': '상담 메모가 추가되었습니다.', 'note_id': note_id}), 201
    else:
        return jsonify({'success': False, 'message': '상담 메모 추가 중 오류가 발생했습니다.'}), 500

@students_bp.route('/my-dashboard', methods=['GET'])
def get_my_dashboard():
    """학생 본인 대시보드 (학생 전용)"""
    if 'user_id' not in session or session.get('role') != 'student':
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    student_id = session['user_id']

    # 수강 중인 과정
    enrollments = execute_query('''
        SELECT e.*, c.class_name, co.course_name, u.full_name as instructor_name
        FROM enrollments e
        INNER JOIN classes c ON e.class_id = c.id
        INNER JOIN courses co ON c.course_id = co.id
        LEFT JOIN users u ON c.instructor_id = u.id
        WHERE e.student_id = ? AND e.status = 'active'
    ''', (student_id,), fetch_all=True)

    # 최근 시험 결과
    recent_exams = execute_query('''
        SELECT ea.*, e.exam_name, e.pass_score
        FROM exam_attempts ea
        INNER JOIN exams e ON ea.exam_id = e.id
        WHERE ea.student_id = ? AND ea.status = 'completed'
        ORDER BY ea.submit_time DESC
        LIMIT 5
    ''', (student_id,), fetch_all=True)

    # 출결 통계
    attendance_stats = execute_query('''
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
            SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
        FROM attendance
        WHERE student_id = ?
    ''', (student_id,), fetch_one=True)

    # 학습 진도
    progress = execute_query('''
        SELECT lp.*, m.title as material_title
        FROM learning_progress lp
        INNER JOIN materials m ON lp.material_id = m.id
        WHERE lp.student_id = ?
        ORDER BY lp.last_accessed DESC
        LIMIT 5
    ''', (student_id,), fetch_all=True)

    return jsonify({
        'success': True,
        'dashboard': {
            'enrollments': enrollments,
            'recent_exams': recent_exams,
            'attendance_stats': attendance_stats,
            'progress': progress
        }
    })
