import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../store/useAuthStore';
import { useStudyCafeStore } from '../store/useStudyCafeStore';
import { FiArrowLeft, FiUsers, FiGrid, FiDollarSign, FiActivity } from 'react-icons/fi';

const AdminPage = () => {
  const navigate = useNavigate();
  const { user } = useAuthStore();
  const { seats, sessions, usageHistory } = useStudyCafeStore();

  if (!user || !user.isAdmin) {
    navigate('/');
    return null;
  }

  const activeSessions = sessions.filter((s) => s.status === 'active');
  const availableSeats = seats.filter((s) => s.status === 'available').length;
  const occupiedSeats = seats.filter((s) => s.status === 'occupied').length;
  const totalRevenue = usageHistory.reduce((sum, h) => sum + h.amount, 0);

  const getSeatStatus = (seatId: string) => {
    const session = activeSessions.find((s) => s.seatId === seatId);
    return session ? 'occupied' : 'available';
  };

  const getSeatColor = (status: string) => {
    switch (status) {
      case 'available':
        return 'bg-green-500';
      case 'occupied':
        return 'bg-red-500';
      default:
        return 'bg-gray-500';
    }
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

        <div className="max-w-6xl mx-auto">
          <h1 className="text-4xl font-bold text-center mb-8 text-gray-800">
            관리자 대시보드
          </h1>

          {/* Statistics Cards */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div className="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl p-6 shadow-lg">
              <div className="flex items-center justify-between mb-4">
                <FiGrid size={32} />
              </div>
              <div className="text-3xl font-bold mb-1">{seats.length}</div>
              <div className="text-blue-100">전체 좌석</div>
            </div>

            <div className="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl p-6 shadow-lg">
              <div className="flex items-center justify-between mb-4">
                <FiActivity size={32} />
              </div>
              <div className="text-3xl font-bold mb-1">{availableSeats}</div>
              <div className="text-green-100">이용 가능</div>
            </div>

            <div className="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-2xl p-6 shadow-lg">
              <div className="flex items-center justify-between mb-4">
                <FiUsers size={32} />
              </div>
              <div className="text-3xl font-bold mb-1">{occupiedSeats}</div>
              <div className="text-red-100">사용 중</div>
            </div>

            <div className="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl p-6 shadow-lg">
              <div className="flex items-center justify-between mb-4">
                <FiDollarSign size={32} />
              </div>
              <div className="text-3xl font-bold mb-1">
                {(totalRevenue / 10000).toFixed(0)}만
              </div>
              <div className="text-purple-100">총 매출</div>
            </div>
          </div>

          {/* Seat Status */}
          <div className="bg-white rounded-2xl shadow-md p-8 mb-8">
            <h2 className="text-2xl font-bold mb-6 text-gray-800">좌석 현황</h2>
            <div className="grid grid-cols-10 gap-3">
              {seats.map((seat) => {
                const status = getSeatStatus(seat.id);
                return (
                  <div
                    key={seat.id}
                    className={`
                      ${getSeatColor(status)}
                      text-white p-4 rounded-lg text-center
                      flex flex-col items-center justify-center
                    `}
                  >
                    <div className="font-bold text-lg">{seat.number}</div>
                  </div>
                );
              })}
            </div>
            <div className="flex gap-6 mt-6">
              <div className="flex items-center gap-2">
                <div className="w-4 h-4 bg-green-500 rounded"></div>
                <span className="text-sm text-gray-600">이용 가능</span>
              </div>
              <div className="flex items-center gap-2">
                <div className="w-4 h-4 bg-red-500 rounded"></div>
                <span className="text-sm text-gray-600">사용 중</span>
              </div>
            </div>
          </div>

          {/* Active Sessions */}
          <div className="bg-white rounded-2xl shadow-md p-8 mb-8">
            <h2 className="text-2xl font-bold mb-6 text-gray-800">
              현재 이용 중인 세션
            </h2>

            {activeSessions.length === 0 ? (
              <p className="text-center text-gray-500 py-8">
                현재 이용 중인 세션이 없습니다.
              </p>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">
                        좌석
                      </th>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">
                        사용자 ID
                      </th>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">
                        입실 시간
                      </th>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">
                        남은 시간
                      </th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-200">
                    {activeSessions.map((session) => {
                      const seat = seats.find((s) => s.id === session.seatId);
                      return (
                        <tr key={session.id} className="hover:bg-gray-50">
                          <td className="px-4 py-4 font-semibold">
                            {seat?.number}번
                          </td>
                          <td className="px-4 py-4 text-gray-600">
                            {session.userId}
                          </td>
                          <td className="px-4 py-4 text-gray-600">
                            {new Date(session.startTime).toLocaleString('ko-KR')}
                          </td>
                          <td className="px-4 py-4 text-gray-600">
                            {session.remainingMinutes}분
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            )}
          </div>

          {/* Recent Usage History */}
          <div className="bg-white rounded-2xl shadow-md p-8">
            <h2 className="text-2xl font-bold mb-6 text-gray-800">
              최근 이용 내역
            </h2>

            {usageHistory.length === 0 ? (
              <p className="text-center text-gray-500 py-8">
                이용 내역이 없습니다.
              </p>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">
                        좌석
                      </th>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">
                        이용권
                      </th>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">
                        입실 시간
                      </th>
                      <th className="px-4 py-3 text-left text-sm font-semibold text-gray-600">
                        퇴실 시간
                      </th>
                      <th className="px-4 py-3 text-right text-sm font-semibold text-gray-600">
                        금액
                      </th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-200">
                    {usageHistory.slice(-10).reverse().map((history) => (
                      <tr key={history.id} className="hover:bg-gray-50">
                        <td className="px-4 py-4 font-semibold">
                          {history.seatNumber}번
                        </td>
                        <td className="px-4 py-4 text-gray-600">
                          {history.ticketName}
                        </td>
                        <td className="px-4 py-4 text-gray-600 text-sm">
                          {new Date(history.startTime).toLocaleString('ko-KR', {
                            month: '2-digit',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit',
                          })}
                        </td>
                        <td className="px-4 py-4 text-gray-600 text-sm">
                          {new Date(history.endTime).toLocaleString('ko-KR', {
                            month: '2-digit',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit',
                          })}
                        </td>
                        <td className="px-4 py-4 text-right font-semibold text-blue-600">
                          {history.amount.toLocaleString()}원
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default AdminPage;
