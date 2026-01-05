from flask import Blueprint, request, jsonify, session
from utils.database import execute_query
import json

materials_bp = Blueprint('materials', __name__, url_prefix='/api/materials')

@materials_bp.route('/', methods=['GET'])
def get_materials():
    """교재 목록 조회"""
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': '로그인이 필요합니다.'}), 401

    # 필터링 파라미터
    course_id = request.args.get('course_id')
    difficulty = request.args.get('difficulty')
    file_type = request.args.get('file_type')
    search = request.args.get('search')

    query = '''
        SELECT m.*, c.course_name, u.full_name as uploader_name
        FROM materials m
        LEFT JOIN courses c ON m.course_id = c.id
        LEFT JOIN users u ON m.uploaded_by = u.id
        WHERE 1=1
    '''
    params = []

    if course_id:
        query += ' AND m.course_id = ?'
        params.append(course_id)

    if difficulty:
        query += ' AND m.difficulty = ?'
        params.append(difficulty)

    if file_type:
        query += ' AND m.file_type = ?'
        params.append(file_type)

    if search:
        query += ' AND (m.title LIKE ? OR m.description LIKE ? OR m.tags LIKE ?)'
        search_param = f'%{search}%'
        params.extend([search_param, search_param, search_param])

    query += ' ORDER BY m.upload_date DESC'

    materials = execute_query(query, tuple(params) if params else None, fetch_all=True)
    return jsonify({'success': True, 'materials': materials})

@materials_bp.route('/<int:material_id>', methods=['GET'])
def get_material(material_id):
    """교재 상세 조회"""
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': '로그인이 필요합니다.'}), 401

    material = execute_query('''
        SELECT m.*, c.course_name, u.full_name as uploader_name
        FROM materials m
        LEFT JOIN courses c ON m.course_id = c.id
        LEFT JOIN users u ON m.uploaded_by = u.id
        WHERE m.id = ?
    ''', (material_id,), fetch_one=True)

    if not material:
        return jsonify({'success': False, 'message': '교재를 찾을 수 없습니다.'}), 404

    return jsonify({'success': True, 'material': material})

@materials_bp.route('/', methods=['POST'])
def create_material():
    """교재 등록"""
    if 'user_id' not in session or session.get('role') not in ['admin', 'instructor', 'system_admin']:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    data = request.get_json()

    material_id = execute_query('''
        INSERT INTO materials (title, description, file_path, file_type, video_url, course_id,
                              tags, difficulty, version, uploaded_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ''', (data['title'], data.get('description', ''), data.get('file_path', ''),
          data.get('file_type', ''), data.get('video_url', ''), data.get('course_id'),
          json.dumps(data.get('tags', [])), data.get('difficulty', 'intermediate'),
          data.get('version', 1), session['user_id']),
        commit=True
    )

    if material_id:
        return jsonify({'success': True, 'message': '교재가 등록되었습니다.', 'material_id': material_id}), 201
    else:
        return jsonify({'success': False, 'message': '교재 등록 중 오류가 발생했습니다.'}), 500

@materials_bp.route('/<int:material_id>', methods=['PUT'])
def update_material(material_id):
    """교재 수정"""
    if 'user_id' not in session or session.get('role') not in ['admin', 'instructor', 'system_admin']:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    data = request.get_json()

    execute_query('''
        UPDATE materials
        SET title = ?, description = ?, file_type = ?, video_url = ?,
            tags = ?, difficulty = ?, version = ?
        WHERE id = ?
    ''', (data['title'], data.get('description', ''), data.get('file_type', ''),
          data.get('video_url', ''), json.dumps(data.get('tags', [])),
          data.get('difficulty', 'intermediate'), data.get('version', 1), material_id),
        commit=True
    )

    return jsonify({'success': True, 'message': '교재가 수정되었습니다.'})

@materials_bp.route('/<int:material_id>', methods=['DELETE'])
def delete_material(material_id):
    """교재 삭제"""
    if 'user_id' not in session or session.get('role') not in ['admin', 'instructor', 'system_admin']:
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    execute_query('DELETE FROM materials WHERE id = ?', (material_id,), commit=True)
    return jsonify({'success': True, 'message': '교재가 삭제되었습니다.'})

@materials_bp.route('/<int:material_id>/progress', methods=['POST'])
def update_progress(material_id):
    """학습 진도 업데이트"""
    if 'user_id' not in session or session.get('role') != 'student':
        return jsonify({'success': False, 'message': '권한이 없습니다.'}), 403

    data = request.get_json()
    progress = data.get('progress', 0)
    completed = data.get('completed', False)

    # 기존 진도 확인
    existing = execute_query('''
        SELECT id FROM learning_progress
        WHERE student_id = ? AND material_id = ?
    ''', (session['user_id'], material_id), fetch_one=True)

    if existing:
        # 업데이트
        execute_query('''
            UPDATE learning_progress
            SET progress_percentage = ?, last_accessed = CURRENT_TIMESTAMP, completed = ?
            WHERE id = ?
        ''', (progress, completed, existing['id']), commit=True)
    else:
        # 생성
        execute_query('''
            INSERT INTO learning_progress (student_id, material_id, progress_percentage, completed)
            VALUES (?, ?, ?, ?)
        ''', (session['user_id'], material_id, progress, completed), commit=True)

    return jsonify({'success': True, 'message': '학습 진도가 업데이트되었습니다.'})
