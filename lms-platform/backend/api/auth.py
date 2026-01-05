from flask import Blueprint, request, jsonify, session
from utils.database import execute_query

auth_bp = Blueprint('auth', __name__, url_prefix='/api/auth')

@auth_bp.route('/login', methods=['POST'])
def login():
    """로그인"""
    data = request.get_json()
    username = data.get('username')
    password = data.get('password')

    if not username or not password:
        return jsonify({'success': False, 'message': '아이디와 비밀번호를 입력해주세요.'}), 400

    # 사용자 조회
    user = execute_query(
        'SELECT * FROM users WHERE username = ? AND password = ? AND is_active = 1',
        (username, password),
        fetch_one=True
    )

    if user:
        # 세션에 사용자 정보 저장
        session['user_id'] = user['id']
        session['username'] = user['username']
        session['role'] = user['role']
        session['full_name'] = user['full_name']

        return jsonify({
            'success': True,
            'message': '로그인 성공',
            'user': {
                'id': user['id'],
                'username': user['username'],
                'full_name': user['full_name'],
                'role': user['role'],
                'email': user['email']
            }
        })
    else:
        return jsonify({'success': False, 'message': '아이디 또는 비밀번호가 올바르지 않습니다.'}), 401

@auth_bp.route('/logout', methods=['POST'])
def logout():
    """로그아웃"""
    session.clear()
    return jsonify({'success': True, 'message': '로그아웃되었습니다.'})

@auth_bp.route('/check', methods=['GET'])
def check_auth():
    """로그인 상태 확인"""
    if 'user_id' in session:
        return jsonify({
            'success': True,
            'authenticated': True,
            'user': {
                'id': session['user_id'],
                'username': session['username'],
                'role': session['role'],
                'full_name': session['full_name']
            }
        })
    else:
        return jsonify({'success': False, 'authenticated': False}), 401

@auth_bp.route('/register', methods=['POST'])
def register():
    """회원가입"""
    data = request.get_json()

    required_fields = ['username', 'password', 'email', 'full_name', 'role']
    for field in required_fields:
        if field not in data:
            return jsonify({'success': False, 'message': f'{field}는 필수 항목입니다.'}), 400

    # 중복 체크
    existing_user = execute_query(
        'SELECT id FROM users WHERE username = ? OR email = ?',
        (data['username'], data['email']),
        fetch_one=True
    )

    if existing_user:
        return jsonify({'success': False, 'message': '이미 존재하는 사용자입니다.'}), 409

    # 사용자 생성
    user_id = execute_query(
        '''INSERT INTO users (username, password, email, full_name, role, phone, is_active)
           VALUES (?, ?, ?, ?, ?, ?, ?)''',
        (data['username'], data['password'], data['email'], data['full_name'],
         data['role'], data.get('phone', ''), 1),
        commit=True
    )

    if user_id:
        return jsonify({
            'success': True,
            'message': '회원가입이 완료되었습니다.',
            'user_id': user_id
        }), 201
    else:
        return jsonify({'success': False, 'message': '회원가입 중 오류가 발생했습니다.'}), 500
