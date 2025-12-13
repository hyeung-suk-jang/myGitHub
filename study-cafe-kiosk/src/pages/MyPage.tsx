import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../store/useAuthStore';
import { useStudyCafeStore } from '../store/useStudyCafeStore';
import { FiArrowLeft, FiClock, FiMapPin, FiLogOut, FiList } from 'react-icons/fi';

const MyPage = () => {
  const navigate = useNavigate();
  const { user } = useAuthStore();
  const { getActiveSession, getUserHistory, endSession, seats, tickets } = useStudyCafeStore();

  if (!user) {
    navigate('/login');
    return null;
  }

  const activeSession = getActiveSession(user.id);
  const history = getUserHistory(user.id);

  const handleEndSession = () => {
    if (activeSession && window.confirm('정말 퇴실하시겠습니까?')) {
      endSession(activeSession.id);
    }
  };

  const getSeatNumber = (seatId: string) => {
    return seats.find((s) => s.id === seatId)?.number || '-';
  };

  const getTicketName = (ticketId: string) => {
    return tickets.find((t) => t.id === ticketId)?.name || '-';
  };

  const formatDate = (date: Date) => {
    return new Date(date).toLocaleString('ko-KR', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const formatDuration = (minutes: number) => {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    if (hours > 0) {
      return `${hours}시간 ${mins}분`;
    }
    return `${mins}분`;
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        <button
          onClick={() => navigate('/')}
          className="text-blue-600 mb-6 flex items-center gap-2 hover:underline"
        >
          <FiArrowLeft />
          홈으로
        </button>

        <div className="max-w-4xl mx-auto">
          <h1 className="text-4xl font-bold text-center mb-8 text-gray-800">
            마이페이지
          </h1>

          {/* User Info */}
          <div className="bg-white rounded-2xl shadow-md p-8 mb-8">
            <h2 className="text-2xl font-bold mb-6 text-gray-800">회원 정보</h2>
            <div className="space-y-4">
              <div className="flex justify-between py-3 border-b">
                <span className="text-gray-600">이름</span>
                <span className="font-semibold">{user.username}</span>
              </div>
              <div className="flex justify-between py-3 border-b">
                <span className="text-gray-600">이메일</span>
                <span className="font-semibold">{user.email}</span>
              </div>
              <div className="flex justify-between py-3">
                <span className="text-gray-600">전화번호</span>
                <span className="font-semibold">{user.phone}</span>
              </div>
            </div>
          </div>

          {/* Active Session */}
          {activeSession && (
            <div className="bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-2xl shadow-md p-8 mb-8">
              <h2 className="text-2xl font-bold mb-6 flex items-center gap-2">
                <FiClock size={28} />
                현재 이용 중
              </h2>

              <div className="space-y-4 mb-6">
                <div className="flex items-center gap-3 text-lg">
                  <FiMapPin size={24} />
                  <span>좌석: {getSeatNumber(activeSession.seatId)}번</span>
                </div>
                <div className="text-lg">
                  <div>이용권: {getTicketName(activeSession.ticketId)}</div>
                </div>
                <div className="text-lg">
                  <div>입실 시간: {formatDate(activeSession.startTime)}</div>
                </div>
                <div className="text-3xl font-bold mt-4">
                  남은 시간: {formatDuration(activeSession.remainingMinutes)}
                </div>
              </div>

              <button
                onClick={handleEndSession}
                className="bg-white text-green-600 px-8 py-3 rounded-lg font-bold hover:bg-gray-100 transition flex items-center gap-2"
              >
                <FiLogOut />
                퇴실하기
              </button>
            </div>
          )}

          {/* Usage History */}
          <div className="bg-white rounded-2xl shadow-md p-8">
            <h2 className="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
              <FiList size={28} />
              이용 내역
            </h2>

            {history.length === 0 ? (
              <p className="text-center text-gray-500 py-8">
                이용 내역이 없습니다.
              </p>
            ) : (
              <div className="space-y-4">
                {history.map((item) => (
                  <div
                    key={item.id}
                    className="border rounded-lg p-6 hover:shadow-md transition"
                  >
                    <div className="flex justify-between items-start mb-4">
                      <div>
                        <div className="font-semibold text-lg mb-1">
                          {item.seatNumber}번 좌석
                        </div>
                        <div className="text-gray-600 text-sm">
                          {item.ticketName}
                        </div>
                      </div>
                      <div className="text-right">
                        <div className="font-bold text-blue-600 text-xl">
                          {item.amount.toLocaleString()}원
                        </div>
                      </div>
                    </div>

                    <div className="text-sm text-gray-600 space-y-1">
                      <div className="flex justify-between">
                        <span>입실</span>
                        <span>{formatDate(item.startTime)}</span>
                      </div>
                      <div className="flex justify-between">
                        <span>퇴실</span>
                        <span>{formatDate(item.endTime)}</span>
                      </div>
                      <div className="flex justify-between font-semibold text-gray-800 pt-2 border-t">
                        <span>이용 시간</span>
                        <span>{formatDuration(item.duration)}</span>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default MyPage;
