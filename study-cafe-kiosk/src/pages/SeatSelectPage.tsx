import { useNavigate } from 'react-router-dom';
import { useStudyCafeStore } from '../store/useStudyCafeStore';
import type { Seat } from '../types';
import { FiArrowLeft } from 'react-icons/fi';

const SeatSelectPage = () => {
  const navigate = useNavigate();
  const { seats, selectSeat } = useStudyCafeStore();

  const handleSeatSelect = (seat: Seat) => {
    if (seat.status === 'available') {
      selectSeat(seat);
      navigate('/tickets');
    }
  };

  const getSeatColor = (status: Seat['status']) => {
    switch (status) {
      case 'available':
        return 'bg-green-500 hover:bg-green-600 cursor-pointer';
      case 'occupied':
        return 'bg-red-500 cursor-not-allowed';
      case 'reserved':
        return 'bg-yellow-500 cursor-not-allowed';
      default:
        return 'bg-gray-500';
    }
  };

  const getSeatIcon = (type: Seat['type']) => {
    switch (type) {
      case 'single':
        return '👤';
      case 'double':
        return '👥';
      case 'group':
        return '👨‍👩‍👧‍👦';
      default:
        return '🪑';
    }
  };

  const floor1Seats = seats.filter((seat) => seat.floor === 1);
  const floor2Seats = seats.filter((seat) => seat.floor === 2);

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

        <h1 className="text-4xl font-bold text-center mb-8 text-gray-800">
          좌석 선택
        </h1>

        {/* Legend */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-8">
          <h3 className="font-semibold mb-4">상태 안내</h3>
          <div className="flex flex-wrap gap-6">
            <div className="flex items-center gap-2">
              <div className="w-6 h-6 bg-green-500 rounded"></div>
              <span className="text-sm">이용 가능</span>
            </div>
            <div className="flex items-center gap-2">
              <div className="w-6 h-6 bg-red-500 rounded"></div>
              <span className="text-sm">사용 중</span>
            </div>
            <div className="flex items-center gap-2">
              <div className="w-6 h-6 bg-yellow-500 rounded"></div>
              <span className="text-sm">예약됨</span>
            </div>
          </div>
          <div className="flex flex-wrap gap-6 mt-4">
            <div className="flex items-center gap-2">
              <span className="text-2xl">👤</span>
              <span className="text-sm">1인석</span>
            </div>
            <div className="flex items-center gap-2">
              <span className="text-2xl">👥</span>
              <span className="text-sm">2인석</span>
            </div>
            <div className="flex items-center gap-2">
              <span className="text-2xl">👨‍👩‍👧‍👦</span>
              <span className="text-sm">단체석</span>
            </div>
          </div>
        </div>

        {/* Floor 1 */}
        <div className="bg-white rounded-lg shadow-md p-8 mb-8">
          <h2 className="text-2xl font-bold mb-6 text-gray-800">1층</h2>
          <div className="grid grid-cols-5 gap-4">
            {floor1Seats.map((seat) => (
              <button
                key={seat.id}
                onClick={() => handleSeatSelect(seat)}
                disabled={seat.status !== 'available'}
                className={`
                  ${getSeatColor(seat.status)}
                  text-white p-6 rounded-lg transition
                  flex flex-col items-center justify-center
                  disabled:opacity-70
                `}
              >
                <div className="text-3xl mb-2">{getSeatIcon(seat.type)}</div>
                <div className="font-semibold text-lg">{seat.number}</div>
              </button>
            ))}
          </div>
        </div>

        {/* Floor 2 */}
        <div className="bg-white rounded-lg shadow-md p-8">
          <h2 className="text-2xl font-bold mb-6 text-gray-800">2층</h2>
          <div className="grid grid-cols-5 gap-4">
            {floor2Seats.map((seat) => (
              <button
                key={seat.id}
                onClick={() => handleSeatSelect(seat)}
                disabled={seat.status !== 'available'}
                className={`
                  ${getSeatColor(seat.status)}
                  text-white p-6 rounded-lg transition
                  flex flex-col items-center justify-center
                  disabled:opacity-70
                `}
              >
                <div className="text-3xl mb-2">{getSeatIcon(seat.type)}</div>
                <div className="font-semibold text-lg">{seat.number}</div>
              </button>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};

export default SeatSelectPage;
