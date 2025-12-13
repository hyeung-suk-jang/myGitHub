import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../store/useAuthStore';
import { useStudyCafeStore } from '../store/useStudyCafeStore';
import { FiArrowLeft, FiCreditCard, FiDollarSign, FiSmartphone, FiCheck } from 'react-icons/fi';

const PaymentPage = () => {
  const navigate = useNavigate();
  const { user } = useAuthStore();
  const { selectedSeat, selectedTicket, startSession } = useStudyCafeStore();
  const [paymentMethod, setPaymentMethod] = useState<'card' | 'cash' | 'transfer'>('card');
  const [isProcessing, setIsProcessing] = useState(false);
  const [isCompleted, setIsCompleted] = useState(false);

  if (!selectedSeat || !selectedTicket || !user) {
    navigate('/');
    return null;
  }

  const handlePayment = async () => {
    setIsProcessing(true);

    // Mock payment process
    await new Promise((resolve) => setTimeout(resolve, 2000));

    // Start session
    startSession(user.id, selectedSeat.id, selectedTicket.id);

    setIsProcessing(false);
    setIsCompleted(true);

    // Redirect after 2 seconds
    setTimeout(() => {
      navigate('/');
    }, 2000);
  };

  if (isCompleted) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="bg-white rounded-2xl shadow-2xl p-12 max-w-md text-center">
          <div className="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <FiCheck size={48} className="text-white" />
          </div>
          <h2 className="text-3xl font-bold text-gray-800 mb-4">
            결제 완료!
          </h2>
          <p className="text-gray-600 mb-2">
            {selectedSeat.number}번 좌석이 배정되었습니다.
          </p>
          <p className="text-gray-600">
            즐거운 학습 되세요!
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        <button
          onClick={() => navigate('/tickets')}
          className="text-blue-600 mb-6 flex items-center gap-2 hover:underline"
        >
          <FiArrowLeft />
          이용권 선택으로
        </button>

        <div className="max-w-2xl mx-auto">
          <h1 className="text-4xl font-bold text-center mb-8 text-gray-800">
            결제
          </h1>

          {/* Order Summary */}
          <div className="bg-white rounded-2xl shadow-md p-8 mb-8">
            <h2 className="text-2xl font-bold mb-6 text-gray-800">주문 내역</h2>

            <div className="space-y-4 mb-6">
              <div className="flex justify-between py-3 border-b">
                <span className="text-gray-600">좌석</span>
                <span className="font-semibold">{selectedSeat.number}번</span>
              </div>
              <div className="flex justify-between py-3 border-b">
                <span className="text-gray-600">이용권</span>
                <span className="font-semibold">{selectedTicket.name}</span>
              </div>
              <div className="flex justify-between py-3">
                <span className="text-gray-600">이용 시간</span>
                <span className="font-semibold">
                  {selectedTicket.duration >= 60
                    ? `${Math.floor(selectedTicket.duration / 60)}시간`
                    : `${selectedTicket.duration}분`}
                </span>
              </div>
            </div>

            <div className="border-t-2 pt-4">
              <div className="flex justify-between items-center">
                <span className="text-xl font-semibold">총 결제 금액</span>
                <span className="text-3xl font-bold text-blue-600">
                  {selectedTicket.price.toLocaleString()}원
                </span>
              </div>
            </div>
          </div>

          {/* Payment Method */}
          <div className="bg-white rounded-2xl shadow-md p-8 mb-8">
            <h2 className="text-2xl font-bold mb-6 text-gray-800">결제 수단</h2>

            <div className="space-y-4">
              <button
                onClick={() => setPaymentMethod('card')}
                className={`
                  w-full p-6 rounded-xl border-2 transition
                  flex items-center gap-4
                  ${paymentMethod === 'card'
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-gray-200 hover:border-blue-300'
                  }
                `}
              >
                <FiCreditCard size={32} className={paymentMethod === 'card' ? 'text-blue-600' : 'text-gray-400'} />
                <div className="flex-1 text-left">
                  <div className="font-semibold text-lg">신용/체크카드</div>
                  <div className="text-sm text-gray-500">카드로 결제</div>
                </div>
                {paymentMethod === 'card' && (
                  <FiCheck size={24} className="text-blue-600" />
                )}
              </button>

              <button
                onClick={() => setPaymentMethod('transfer')}
                className={`
                  w-full p-6 rounded-xl border-2 transition
                  flex items-center gap-4
                  ${paymentMethod === 'transfer'
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-gray-200 hover:border-blue-300'
                  }
                `}
              >
                <FiSmartphone size={32} className={paymentMethod === 'transfer' ? 'text-blue-600' : 'text-gray-400'} />
                <div className="flex-1 text-left">
                  <div className="font-semibold text-lg">간편결제</div>
                  <div className="text-sm text-gray-500">카카오페이, 네이버페이 등</div>
                </div>
                {paymentMethod === 'transfer' && (
                  <FiCheck size={24} className="text-blue-600" />
                )}
              </button>

              <button
                onClick={() => setPaymentMethod('cash')}
                className={`
                  w-full p-6 rounded-xl border-2 transition
                  flex items-center gap-4
                  ${paymentMethod === 'cash'
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-gray-200 hover:border-blue-300'
                  }
                `}
              >
                <FiDollarSign size={32} className={paymentMethod === 'cash' ? 'text-blue-600' : 'text-gray-400'} />
                <div className="flex-1 text-left">
                  <div className="font-semibold text-lg">현금</div>
                  <div className="text-sm text-gray-500">현금으로 결제</div>
                </div>
                {paymentMethod === 'cash' && (
                  <FiCheck size={24} className="text-blue-600" />
                )}
              </button>
            </div>
          </div>

          {/* Pay Button */}
          <button
            onClick={handlePayment}
            disabled={isProcessing}
            className="w-full bg-blue-600 text-white py-6 rounded-xl text-xl font-bold hover:bg-blue-700 transition disabled:bg-gray-400 disabled:cursor-not-allowed"
          >
            {isProcessing ? (
              <div className="flex items-center justify-center gap-3">
                <div className="w-6 h-6 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
                <span>결제 처리 중...</span>
              </div>
            ) : (
              `${selectedTicket.price.toLocaleString()}원 결제하기`
            )}
          </button>
        </div>
      </div>
    </div>
  );
};

export default PaymentPage;
