from flask import Blueprint, request, jsonify, session
from utils.database import execute_query, get_db_connection
import json
from datetime import datetime

exams_bp = Blueprint('exams', __name__, url_prefix='/api/exams')

@exams_bp.route('/questions', methods=['GET'])
def get_questions():
    """문제 은행 조회"""
    if 'user_id' not in session or session.get('role') not in ['admin', 'instructor', 'system_admin']:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    # 필터링 파라미터
    category = request.args.get('category')
    difficulty = request.args.get('difficulty')
    keyword = request.args.get('keyword')

    query = 'SELECT * FROM questions WHERE 1=1'
    params = []

    if category:
        query += ' AND category = ?'
        params.append(category)

    if difficulty:
        query += ' AND difficulty = ?'
        params.append(difficulty)

    if keyword:
        query += ' AND (question_text LIKE ? OR keywords LIKE ?)'
        params.extend([f'%{keyword}%', f'%{keyword}%'])

    query += ' ORDER BY created_at DESC'

    questions = execute_query(query, tuple(params) if params else None, fetch_all=True)
    return jsonify({'success': True, 'questions': questions})

@exams_bp.route('/questions', methods=['POST'])
def create_question():
    """문제 생성"""
    if 'user_id' not in session or session.get('role') not in ['admin', 'instructor', 'system_admin']:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    data = request.get_json()

    question_id = execute_query(
        '''INSERT INTO questions (question_text, question_type, category, chapter, difficulty, keywords,
           correct_answer, options, points, created_by)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)''',
        (data['question_text'], data['question_type'], data.get('category', ''),
         data.get('chapter', ''), data.get('difficulty', 'medium'),
         json.dumps(data.get('keywords', [])), json.dumps(data.get('correct_answer')),
         json.dumps(data.get('options', [])), data.get('points', 1), session['user_id']),
        commit=True
    )

    if question_id:
        return jsonify({'success': True, 'message': '문제가 생성되었습니다.', 'question_id': question_id}), 201
    else:
        return jsonify({'success': False, 'message': '문제 생성 중 오류가 발생했습니다.'}), 500

@exams_bp.route('/questions/<int:question_id>', methods=['PUT'])
def update_question(question_id):
    """문제 수정"""
    if 'user_id' not in session or session.get('role') not in ['admin', 'instructor', 'system_admin']:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    data = request.get_json()

    execute_query(
        '''UPDATE questions SET question_text = ?, question_type = ?, category = ?, chapter = ?,
           difficulty = ?, keywords = ?, correct_answer = ?, options = ?, points = ?
           WHERE id = ?''',
        (data['question_text'], data['question_type'], data.get('category', ''),
         data.get('chapter', ''), data.get('difficulty', 'medium'),
         json.dumps(data.get('keywords', [])), json.dumps(data.get('correct_answer')),
         json.dumps(data.get('options', [])), data.get('points', 1), question_id),
        commit=True
    )

    return jsonify({'success': True, 'message': '문제가 수정되었습니다.'})

@exams_bp.route('/questions/<int:question_id>', methods=['DELETE'])
def delete_question(question_id):
    """문제 삭제"""
    if 'user_id' not in session or session.get('role') not in ['admin', 'instructor', 'system_admin']:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    execute_query('DELETE FROM questions WHERE id = ?', (question_id,), commit=True)
    return jsonify({'success': True, 'message': '문제가 삭제되었습니다.'})

@exams_bp.route('/', methods=['GET'])
def get_exams():
    """시험 목록 조회"""
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': '로그인이 필요합니다.'}), 401

    role = session.get('role')

    if role in ['admin', 'instructor', 'system_admin']:
        # 관리자/강사: 모든 시험 조회
        exams = execute_query('''
            SELECT e.*, c.course_name, u.full_name as creator_name
            FROM exams e
            LEFT JOIN courses c ON e.course_id = c.id
            LEFT JOIN users u ON e.created_by = u.id
            ORDER BY e.created_at DESC
        ''', fetch_all=True)
    else:
        # 학생: 배정된 시험만 조회
        exams = execute_query('''
            SELECT DISTINCT e.*, c.course_name, u.full_name as creator_name
            FROM exams e
            LEFT JOIN courses c ON e.course_id = c.id
            LEFT JOIN users u ON e.created_by = u.id
            INNER JOIN exam_assignments ea ON e.id = ea.exam_id
            WHERE ea.student_id = ? OR ea.class_id IN (
                SELECT class_id FROM enrollments WHERE student_id = ?
            )
            ORDER BY e.start_time DESC
        ''', (session['user_id'], session['user_id']), fetch_all=True)

    return jsonify({'success': True, 'exams': exams})

@exams_bp.route('/', methods=['POST'])
def create_exam():
    """시험지 생성"""
    if 'user_id' not in session or session.get('role') not in ['admin', 'instructor', 'system_admin']:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    data = request.get_json()

    with get_db_connection() as conn:
        cursor = conn.cursor()

        # 시험 생성
        cursor.execute('''
            INSERT INTO exams (exam_name, description, course_id, duration_minutes, pass_score,
                             shuffle_questions, shuffle_options, allow_retake, max_retakes,
                             start_time, end_time, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ''', (data['exam_name'], data.get('description', ''), data.get('course_id'),
              data.get('duration_minutes', 60), data.get('pass_score', 60),
              data.get('shuffle_questions', 0), data.get('shuffle_options', 0),
              data.get('allow_retake', 0), data.get('max_retakes', 0),
              data.get('start_time'), data.get('end_time'), session['user_id']))

        exam_id = cursor.lastrowid

        # 문제 추가
        if 'questions' in data and data['questions']:
            for idx, question_info in enumerate(data['questions']):
                cursor.execute('''
                    INSERT INTO exam_questions (exam_id, question_id, order_num, points)
                    VALUES (?, ?, ?, ?)
                ''', (exam_id, question_info['question_id'], idx + 1, question_info.get('points', 1)))

        conn.commit()
        cursor.close()

    return jsonify({'success': True, 'message': '시험이 생성되었습니다.', 'exam_id': exam_id}), 201

@exams_bp.route('/<int:exam_id>', methods=['GET'])
def get_exam(exam_id):
    """시험 상세 조회"""
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': '로그인이 필요합니다.'}), 401

    exam = execute_query('''
        SELECT e.*, c.course_name
        FROM exams e
        LEFT JOIN courses c ON e.course_id = c.id
        WHERE e.id = ?
    ''', (exam_id,), fetch_one=True)

    if not exam:
        return jsonify({'success': False, 'message': '시험을 찾을 수 없습니다.'}), 404

    # 문제 목록
    questions = execute_query('''
        SELECT eq.*, q.question_text, q.question_type, q.options
        FROM exam_questions eq
        INNER JOIN questions q ON eq.question_id = q.id
        WHERE eq.exam_id = ?
        ORDER BY eq.order_num
    ''', (exam_id,), fetch_all=True)

    exam['questions'] = questions

    return jsonify({'success': True, 'exam': exam})

@exams_bp.route('/<int:exam_id>/start', methods=['POST'])
def start_exam(exam_id):
    """시험 시작"""
    if 'user_id' not in session or session.get('role') != 'student':
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    # 시험 정보 조회
    exam = execute_query('SELECT * FROM exams WHERE id = ?', (exam_id,), fetch_one=True)

    if not exam:
        return jsonify({'success': False, 'message': '시험을 찾을 수 없습니다.'}), 404

    # 이미 진행 중인 시도가 있는지 확인
    existing_attempt = execute_query('''
        SELECT id FROM exam_attempts
        WHERE exam_id = ? AND student_id = ? AND status = 'in_progress'
    ''', (exam_id, session['user_id']), fetch_one=True)

    if existing_attempt:
        return jsonify({
            'success': True,
            'message': '진행 중인 시험이 있습니다.',
            'attempt_id': existing_attempt['id']
        })

    # 재응시 확인
    attempt_count = execute_query('''
        SELECT COUNT(*) as count FROM exam_attempts
        WHERE exam_id = ? AND student_id = ?
    ''', (exam_id, session['user_id']), fetch_one=True)

    if not exam['allow_retake'] and attempt_count['count'] > 0:
        return jsonify({'success': False, 'message': '재응시가 허용되지 않습니다.'}), 403

    if exam['allow_retake'] and attempt_count['count'] >= exam['max_retakes']:
        return jsonify({'success': False, 'message': '최대 응시 횟수를 초과했습니다.'}), 403

    # 새 시도 생성
    attempt_id = execute_query('''
        INSERT INTO exam_attempts (exam_id, student_id, attempt_number, start_time, status, ip_address)
        VALUES (?, ?, ?, ?, ?, ?)
    ''', (exam_id, session['user_id'], attempt_count['count'] + 1,
          datetime.now().isoformat(), 'in_progress', request.remote_addr),
        commit=True
    )

    return jsonify({
        'success': True,
        'message': '시험이 시작되었습니다.',
        'attempt_id': attempt_id
    }), 201

@exams_bp.route('/attempts/<int:attempt_id>/submit', methods=['POST'])
def submit_exam(attempt_id):
    """시험 제출"""
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': '로그인이 필요합니다.'}), 401

    data = request.get_json()
    answers = data.get('answers', [])

    with get_db_connection() as conn:
        cursor = conn.cursor()

        # 시도 정보 조회
        cursor.execute('SELECT * FROM exam_attempts WHERE id = ? AND student_id = ?',
                      (attempt_id, session['user_id']))
        attempt = cursor.fetchone()

        if not attempt:
            return jsonify({'success': False, 'message': '시험 응시 기록을 찾을 수 없습니다.'}), 404

        # 답안 저장 및 자동 채점
        total_score = 0
        max_score = 0

        for answer in answers:
            question_id = answer['question_id']
            student_answer = answer['answer']

            # 문제 정보 조회
            cursor.execute('SELECT * FROM questions WHERE id = ?', (question_id,))
            question = cursor.fetchone()

            if question:
                correct_answer = json.loads(question['correct_answer'])
                is_correct = False
                points_earned = 0

                # 객관식 자동 채점
                if question['question_type'] == 'multiple_choice':
                    is_correct = student_answer == correct_answer
                    points_earned = question['points'] if is_correct else 0

                # 답안 저장
                cursor.execute('''
                    INSERT INTO student_answers (attempt_id, question_id, student_answer, is_correct, points_earned)
                    VALUES (?, ?, ?, ?, ?)
                ''', (attempt_id, question_id, json.dumps(student_answer), is_correct, points_earned))

                total_score += points_earned
                max_score += question['points']

        # 시험 완료 처리
        cursor.execute('''
            UPDATE exam_attempts
            SET end_time = ?, submit_time = ?, score = ?, status = 'completed'
            WHERE id = ?
        ''', (datetime.now().isoformat(), datetime.now().isoformat(),
              (total_score / max_score * 100) if max_score > 0 else 0, attempt_id))

        conn.commit()
        cursor.close()

    return jsonify({
        'success': True,
        'message': '시험이 제출되었습니다.',
        'score': (total_score / max_score * 100) if max_score > 0 else 0,
        'total_score': total_score,
        'max_score': max_score
    })

@exams_bp.route('/attempts/<int:attempt_id>/results', methods=['GET'])
def get_exam_results(attempt_id):
    """시험 결과 조회"""
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': '로그인이 필요합니다.'}), 401

    # 시도 정보
    attempt = execute_query('''
        SELECT ea.*, e.exam_name, e.pass_score, u.full_name as student_name
        FROM exam_attempts ea
        INNER JOIN exams e ON ea.exam_id = e.id
        INNER JOIN users u ON ea.student_id = u.id
        WHERE ea.id = ?
    ''', (attempt_id,), fetch_one=True)

    if not attempt:
        return jsonify({'success': False, 'message': '시험 응시 기록을 찾을 수 없습니다.'}), 404

    # 권한 확인
    role = session.get('role')
    if role == 'student' and attempt['student_id'] != session['user_id']:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    # 답안 및 정오답
    answers = execute_query('''
        SELECT sa.*, q.question_text, q.question_type, q.correct_answer, q.options, q.points
        FROM student_answers sa
        INNER JOIN questions q ON sa.question_id = q.id
        WHERE sa.attempt_id = ?
    ''', (attempt_id,), fetch_all=True)

    attempt['answers'] = answers
    attempt['passed'] = attempt['score'] >= attempt['pass_score']

    return jsonify({'success': True, 'results': attempt})
