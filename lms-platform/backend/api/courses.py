from flask import Blueprint, request, jsonify, session
from utils.database import execute_query

courses_bp = Blueprint('courses', __name__, url_prefix='/api/courses')

@courses_bp.route('/', methods=['GET'])
def get_courses():
    """과정 목록 조회"""
    courses = execute_query('SELECT * FROM courses WHERE is_active = 1 ORDER BY created_at DESC', fetch_all=True)
    return jsonify({'success': True, 'courses': courses})

@courses_bp.route('/<int:course_id>', methods=['GET'])
def get_course(course_id):
    """특정 과정 조회"""
    course = execute_query('SELECT * FROM courses WHERE id = ?', (course_id,), fetch_one=True)

    if course:
        # 해당 과정의 반 목록
        classes = execute_query('''
            SELECT c.*, u.full_name as instructor_name
            FROM classes c
            LEFT JOIN users u ON c.instructor_id = u.id
            WHERE c.course_id = ?
        ''', (course_id,), fetch_all=True)

        course['classes'] = classes
        return jsonify({'success': True, 'course': course})
    else:
        return jsonify({'success': False, 'message': '과정을 찾을 수 없습니다.'}), 404

@courses_bp.route('/', methods=['POST'])
def create_course():
    """과정 생성 (관리자 전용)"""
    if 'user_id' not in session or session.get('role') not in ['admin', 'system_admin']:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    data = request.get_json()

    course_id = execute_query(
        '''INSERT INTO courses (course_name, course_code, description, start_date, end_date, max_students, is_active)
           VALUES (?, ?, ?, ?, ?, ?, ?)''',
        (data['course_name'], data['course_code'], data.get('description', ''),
         data.get('start_date'), data.get('end_date'), data.get('max_students', 30), 1),
        commit=True
    )

    if course_id:
        return jsonify({'success': True, 'message': '과정이 생성되었습니다.', 'course_id': course_id}), 201
    else:
        return jsonify({'success': False, 'message': '과정 생성 중 오류가 발생했습니다.'}), 500

@courses_bp.route('/<int:course_id>', methods=['PUT'])
def update_course(course_id):
    """과정 수정 (관리자 전용)"""
    if 'user_id' not in session or session.get('role') not in ['admin', 'system_admin']:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    data = request.get_json()

    execute_query(
        '''UPDATE courses SET course_name = ?, description = ?, start_date = ?, end_date = ?, max_students = ?
           WHERE id = ?''',
        (data['course_name'], data.get('description', ''), data.get('start_date'),
         data.get('end_date'), data.get('max_students', 30), course_id),
        commit=True
    )

    return jsonify({'success': True, 'message': '과정이 수정되었습니다.'})

@courses_bp.route('/<int:course_id>', methods=['DELETE'])
def delete_course(course_id):
    """과정 삭제 (소프트 삭제) (관리자 전용)"""
    if 'user_id' not in session or session.get('role') not in ['admin', 'system_admin']:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    execute_query('UPDATE courses SET is_active = 0 WHERE id = ?', (course_id,), commit=True)
    return jsonify({'success': True, 'message': '과정이 삭제되었습니다.'})

@courses_bp.route('/<int:course_id>/classes', methods=['GET'])
def get_course_classes(course_id):
    """과정의 반 목록 조회"""
    classes = execute_query('''
        SELECT c.*, u.full_name as instructor_name
        FROM classes c
        LEFT JOIN users u ON c.instructor_id = u.id
        WHERE c.course_id = ?
    ''', (course_id,), fetch_all=True)

    return jsonify({'success': True, 'classes': classes})

@courses_bp.route('/<int:course_id>/enroll', methods=['POST'])
def enroll_course(course_id):
    """수강 신청"""
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': '로그인이 필요합니다.'}), 401

    data = request.get_json()
    class_id = data.get('class_id')

    if not class_id:
        return jsonify({'success': False, 'message': '반을 선택해주세요.'}), 400

    # 이미 수강 신청했는지 확인
    existing = execute_query(
        'SELECT id FROM enrollments WHERE student_id = ? AND class_id = ?',
        (session['user_id'], class_id),
        fetch_one=True
    )

    if existing:
        return jsonify({'success': False, 'message': '이미 수강 신청한 과정입니다.'}), 409

    # 수강 신청
    enrollment_id = execute_query(
        'INSERT INTO enrollments (student_id, class_id, status) VALUES (?, ?, ?)',
        (session['user_id'], class_id, 'active'),
        commit=True
    )

    if enrollment_id:
        return jsonify({'success': True, 'message': '수강 신청이 완료되었습니다.', 'enrollment_id': enrollment_id}), 201
    else:
        return jsonify({'success': False, 'message': '수강 신청 중 오류가 발생했습니다.'}), 500
