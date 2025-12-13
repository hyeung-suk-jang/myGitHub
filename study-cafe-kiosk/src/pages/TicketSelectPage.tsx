import { useNavigate } from 'react-router-dom';
import { useStudyCafeStore } from '../store/useStudyCafeStore';
import type { Ticket } from '../types';
import { FiArrowLeft, FiClock, FiCheck } from 'react-icons/fi';

const TicketSelectPage = () => {
  const navigate = useNavigate();
  const { tickets, selectedSeat, selectTicket } = useStudyCafeStore();

  if (!selectedSeat) {
    navigate('/seats');
    return null;
  }

  const handleTicketSelect = (ticket: Ticket) => {
    selectTicket(ticket);
    navigate('/payment');
  };

  const getTicketTypeLabel = (type: Ticket['type']) => {
    switch (type) {
      case 'hourly':
        return '시간제';
      case 'daily':
        return '일일권';
      case 'weekly':
        return '주간권';
      case 'monthly':
        return '월간권';
      default:
        return '';
    }
  };

  const getTicketTypeColor = (type: Ticket['type']) => {
    switch (type) {
      case 'hourly':
        return 'from-blue-500 to-blue-600';
      case 'daily':
        return 'from-green-500 to-green-600';
      case 'weekly':
        return 'from-purple-500 to-purple-600';
      case 'monthly':
        return 'from-pink-500 to-pink-600';
      default:
        return 'from-gray-500 to-gray-600';
    }
  };

  const formatDuration = (minutes: number) => {
    if (minutes >= 43200) return '1개월';
    if (minutes >= 10080) return '1주일';
    if (minutes >= 1440) return `${Math.floor(minutes / 1440)}일`;
    if (minutes >= 60) return `${Math.floor(minutes / 60)}시간`;
    return `${minutes}분`;
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        <button
          onClick={() => navigate('/seats')}
          className="text-blue-600 mb-6 flex items-center gap-2 hover:underline"
        >
          <FiArrowLeft />
          좌석 선택으로
        </button>

        <div className="max-w-4xl mx-auto">
          <h1 className="text-4xl font-bold text-center mb-4 text-gray-800">
            이용권 선택
          </h1>

          {/* Selected Seat Info */}
          <div className="bg-blue-100 rounded-lg p-4 mb-8 text-center">
            <p className="text-blue-800 font-semibold">
              선택된 좌석: <span className="text-2xl">{selectedSeat.number}번</span>
            </p>
          </div>

          {/* Tickets Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {tickets.map((ticket) => (
              <button
                key={ticket.id}
                onClick={() => handleTicketSelect(ticket)}
                className={`
                  bg-gradient-to-br ${getTicketTypeColor(ticket.type)}
                  text-white rounded-2xl p-8 shadow-lg
                  hover:shadow-2xl transition-all duration-300
                  transform hover:-translate-y-1
                  text-left
                `}
              >
                <div className="flex justify-between items-start mb-4">
                  <div>
                    <div className="text-sm opacity-90 mb-1">
                      {getTicketTypeLabel(ticket.type)}
                    </div>
                    <h3 className="text-2xl font-bold">{ticket.name}</h3>
                  </div>
                  <FiCheck size={32} className="opacity-0 group-hover:opacity-100" />
                </div>

                <div className="flex items-center gap-2 mb-4 text-lg">
                  <FiClock />
                  <span>{formatDuration(ticket.duration)}</span>
                </div>

                <p className="text-sm opacity-90 mb-6">{ticket.description}</p>

                <div className="flex justify-between items-end">
                  <div className="text-3xl font-bold">
                    {ticket.price.toLocaleString()}원
                  </div>
                </div>
              </button>
            ))}
          </div>

          {/* Info Notice */}
          <div className="mt-8 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
            <p className="text-yellow-800 text-sm">
              <strong>안내:</strong> 이용권 구매 후 환불이 불가능합니다.
              신중하게 선택해 주세요.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default TicketSelectPage;
