import { useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../store/authStore';
import { useCartStore } from '../store/cartStore';

const CartPage = () => {
  const navigate = useNavigate();
  const { isAuthenticated } = useAuthStore();
  const { items, fetchCart, updateQuantity, removeFromCart, getTotal } = useCartStore();

  useEffect(() => {
    if (!isAuthenticated) {
      navigate('/login');
      return;
    }
    fetchCart();
  }, [isAuthenticated]);

  const total = getTotal();
  const shippingFee = total >= 50000 ? 0 : 3000;
  const finalTotal = total + shippingFee;

  const handleUpdateQuantity = async (id: string, quantity: number) => {
    try {
      await updateQuantity(id, quantity);
    } catch (error) {
      alert('수량 변경에 실패했습니다.');
    }
  };

  const handleRemove = async (id: string) => {
    if (confirm('장바구니에서 삭제하시겠습니까?')) {
      try {
        await removeFromCart(id);
      } catch (error) {
        alert('삭제에 실패했습니다.');
      }
    }
  };

  if (items.length === 0) {
    return (
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div className="text-center">
          <h1 className="text-3xl font-bold text-gray-900 mb-4">장바구니</h1>
          <p className="text-gray-600 mb-8">장바구니가 비어있습니다.</p>
          <Link to="/products" className="btn-primary">
            쇼핑 계속하기
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 className="text-3xl font-bold text-gray-900 mb-8">장바구니</h1>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-4">
          {items.map((item) => {
            const images = JSON.parse(item.product.images || '[]');
            const price = item.product.salePrice || item.product.price;
            const itemTotal = price * item.quantity;

            return (
              <div key={item.id} className="card flex gap-4">
                <Link to={`/products/${item.product.id}`} className="w-24 h-24 flex-shrink-0">
                  <img
                    src={images[0] || '/placeholder.png'}
                    alt={item.product.name}
                    className="w-full h-full object-cover rounded"
                  />
                </Link>

                <div className="flex-1">
                  <Link
                    to={`/products/${item.product.id}`}
                    className="font-semibold text-gray-900 hover:text-primary-600"
                  >
                    {item.product.name}
                  </Link>
                  <p className="text-gray-600 mt-1">{price.toLocaleString()}원</p>

                  <div className="flex items-center gap-2 mt-2">
                    <button
                      onClick={() => handleUpdateQuantity(item.id, item.quantity - 1)}
                      disabled={item.quantity <= 1}
                      className="px-2 py-1 border rounded hover:bg-gray-100 disabled:opacity-50"
                    >
                      -
                    </button>
                    <span className="px-4">{item.quantity}</span>
                    <button
                      onClick={() => handleUpdateQuantity(item.id, item.quantity + 1)}
                      disabled={item.quantity >= item.product.stock}
                      className="px-2 py-1 border rounded hover:bg-gray-100 disabled:opacity-50"
                    >
                      +
                    </button>
                  </div>
                </div>

                <div className="text-right">
                  <p className="font-semibold text-lg">{itemTotal.toLocaleString()}원</p>
                  <button
                    onClick={() => handleRemove(item.id)}
                    className="text-sm text-red-600 hover:underline mt-2"
                  >
                    삭제
                  </button>
                </div>
              </div>
            );
          })}
        </div>

        <div className="lg:col-span-1">
          <div className="card sticky top-24">
            <h2 className="text-xl font-semibold mb-4">주문 요약</h2>

            <div className="space-y-2 mb-4">
              <div className="flex justify-between">
                <span className="text-gray-600">상품 금액</span>
                <span>{total.toLocaleString()}원</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">배송비</span>
                <span>{shippingFee.toLocaleString()}원</span>
              </div>
              {total < 50000 && shippingFee > 0 && (
                <p className="text-sm text-gray-500">
                  {(50000 - total).toLocaleString()}원 추가 시 무료배송
                </p>
              )}
            </div>

            <div className="border-t pt-4 mb-6">
              <div className="flex justify-between text-lg font-semibold">
                <span>총 결제 금액</span>
                <span className="text-primary-600">{finalTotal.toLocaleString()}원</span>
              </div>
            </div>

            <Link to="/checkout" className="block w-full btn-primary text-center">
              주문하기
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
};

export default CartPage;
