import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../store/useAuthStore';
import { useStudyCafeStore } from '../store/useStudyCafeStore';
import { FiUser, FiLogIn, FiLogOut, FiClock, FiGrid } from 'react-icons/fi';

const HomePage = () => {
  const navigate = useNavigate();
  const { user, isAuthenticated, logout } = useAuthStore();
  const { getActiveSession } = useStudyCafeStore();

  const activeSession = user ? getActiveSession(user.id) : null;

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-500 to-purple-600">
      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="flex justify-between items-center mb-12">
          <h1 className="text-4xl font-bold text-white">스터디 카페 키오스크</h1>
          {isAuthenticated && (
            <div className="flex items-center gap-4">
              <span className="text-white">{user?.username}님</span>
              <button
                onClick={handleLogout}
                className="bg-white text-blue-600 px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-gray-100 transition"
              >
                <FiLogOut />
                로그아웃
              </button>
            </div>
          )}
        </div>

        {/* Main Content */}
        <div className="max-w-4xl mx-auto">
          {!isAuthenticated ? (
            <div className="bg-white rounded-2xl shadow-2xl p-12">
              <h2 className="text-3xl font-bold text-center mb-8 text-gray-800">
                환영합니다!
              </h2>
              <p className="text-center text-gray-600 mb-12">
                스터디 카페를 이용하시려면 로그인이 필요합니다.
              </p>
              <div className="grid grid-cols-2 gap-6">
                <button
                  onClick={() => navigate('/login')}
                  className="bg-blue-500 text-white py-6 rounded-xl text-xl font-semibold hover:bg-blue-600 transition flex items-center justify-center gap-3"
                >
                  <FiLogIn size={24} />
                  로그인
                </button>
                <button
                  onClick={() => navigate('/register')}
                  className="bg-purple-500 text-white py-6 rounded-xl text-xl font-semibold hover:bg-purple-600 transition flex items-center justify-center gap-3"
                >
                  <FiUser size={24} />
                  회원가입
                </button>
              </div>
            </div>
          ) : (
            <div className="space-y-6">
              {/* Active Session Card */}
              {activeSession && (
                <div className="bg-green-500 text-white rounded-2xl shadow-2xl p-8">
                  <h3 className="text-2xl font-bold mb-4">현재 이용 중</h3>
                  <div className="flex items-center gap-3 text-lg">
                    <FiClock size={24} />
                    <span>남은 시간: {activeSession.remainingMinutes}분</span>
                  </div>
                  <button
                    onClick={() => navigate('/my-page')}
                    className="mt-4 bg-white text-green-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition"
                  >
                    상세 보기
                  </button>
                </div>
              )}

              {/* Menu Grid */}
              <div className="bg-white rounded-2xl shadow-2xl p-8">
                <h2 className="text-2xl font-bold mb-8 text-gray-800">메뉴</h2>
                <div className="grid grid-cols-2 gap-6">
                  {!activeSession && (
                    <button
                      onClick={() => navigate('/seats')}
                      className="bg-blue-500 text-white p-8 rounded-xl hover:bg-blue-600 transition flex flex-col items-center gap-4"
                    >
                      <FiGrid size={48} />
                      <span className="text-xl font-semibold">좌석 선택</span>
                    </button>
                  )}
                  <button
                    onClick={() => navigate('/my-page')}
                    className="bg-purple-500 text-white p-8 rounded-xl hover:bg-purple-600 transition flex flex-col items-center gap-4"
                  >
                    <FiUser size={48} />
                    <span className="text-xl font-semibold">마이페이지</span>
                  </button>
                  {user?.isAdmin && (
                    <button
                      onClick={() => navigate('/admin')}
                      className="bg-red-500 text-white p-8 rounded-xl hover:bg-red-600 transition flex flex-col items-center gap-4"
                    >
                      <FiGrid size={48} />
                      <span className="text-xl font-semibold">관리자</span>
                    </button>
                  )}
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default HomePage;
