from flask import Flask, jsonify, send_from_directory
from flask_cors import CORS
from config.config import config_by_name
from utils.database import init_database, insert_sample_data
import os

# 블루프린트 임포트
from api.auth import auth_bp
from api.courses import courses_bp
from api.exams import exams_bp
from api.materials import materials_bp
from api.students import students_bp

def create_app(config_name='development'):
    """Flask 앱 팩토리"""
    app = Flask(__name__, static_folder='../frontend')

    # 설정 로드
    app.config.from_object(config_by_name[config_name])

    # CORS 설정
    CORS(app, supports_credentials=True)

    # 시크릿 키 설정
    app.secret_key = app.config['SECRET_KEY']

    # 블루프린트 등록
    app.register_blueprint(auth_bp)
    app.register_blueprint(courses_bp)
    app.register_blueprint(exams_bp)
    app.register_blueprint(materials_bp)
    app.register_blueprint(students_bp)

    # 헬스 체크 엔드포인트
    @app.route('/api/health', methods=['GET'])
    def health_check():
        return jsonify({'status': 'ok', 'message': 'LMS API is running'})

    # 정적 파일 서빙 - 사용자 사이트
    @app.route('/user')
    @app.route('/user/<path:path>')
    def serve_user_site(path='index.html'):
        user_dir = os.path.join(app.static_folder, 'user')
        if path != 'index.html' and os.path.exists(os.path.join(user_dir, path)):
            return send_from_directory(user_dir, path)
        return send_from_directory(user_dir, 'index.html')

    # 정적 파일 서빙 - 관리자 사이트
    @app.route('/admin')
    @app.route('/admin/<path:path>')
    def serve_admin_site(path='index.html'):
        admin_dir = os.path.join(app.static_folder, 'admin')
        if path != 'index.html' and os.path.exists(os.path.join(admin_dir, path)):
            return send_from_directory(admin_dir, path)
        return send_from_directory(admin_dir, 'index.html')

    # 루트 경로
    @app.route('/')
    def index():
        return jsonify({
            'message': 'LMS 플랫폼 API',
            'version': '1.0.0',
            'endpoints': {
                'user_site': '/user',
                'admin_site': '/admin',
                'api_docs': '/api',
                'health': '/api/health'
            }
        })

    return app

if __name__ == '__main__':
    # 데이터베이스 초기화
    if not os.path.exists(config_by_name['development'].DATABASE_PATH):
        print("데이터베이스를 초기화합니다...")
        init_database()
        insert_sample_data()
        print("초기화 완료!")

    # 앱 생성 및 실행
    app = create_app('development')
    print("\n" + "="*60)
    print("LMS 플랫폼 서버가 시작되었습니다!")
    print("="*60)
    print(f"사용자 사이트: http://localhost:5000/user")
    print(f"관리자 사이트: http://localhost:5000/admin")
    print(f"API 문서: http://localhost:5000/api")
    print(f"헬스 체크: http://localhost:5000/api/health")
    print("="*60)
    print("\n기본 계정:")
    print("  관리자: admin / admin123")
    print("  강사: instructor1 / instructor123")
    print("  학생: student1 / student123")
    print("="*60 + "\n")

    app.run(host='0.0.0.0', port=5000, debug=True)
