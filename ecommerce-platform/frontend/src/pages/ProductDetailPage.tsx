import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Product } from '../types';
import { useAuthStore } from '../store/authStore';
import { useCartStore } from '../store/cartStore';
import api from '../lib/api';

const ProductDetailPage = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { isAuthenticated } = useAuthStore();
  const { addToCart } = useCartStore();
  const [product, setProduct] = useState<Product | null>(null);
  const [quantity, setQuantity] = useState(1);
  const [loading, setLoading] = useState(true);
  const [selectedImage, setSelectedImage] = useState(0);

  useEffect(() => {
    fetchProduct();
  }, [id]);

  const fetchProduct = async () => {
    try {
      const response = await api.get(`/products/${id}`);
      setProduct(response.data.product);
    } catch (error) {
      console.error('Failed to fetch product:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleAddToCart = async () => {
    if (!isAuthenticated) {
      alert('로그인이 필요합니다.');
      navigate('/login');
      return;
    }

    try {
      await addToCart(product!.id, quantity);
      alert('장바구니에 추가되었습니다.');
    } catch (error) {
      alert('장바구니 추가에 실패했습니다.');
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-[60vh]">
        <div className="text-lg text-gray-600">로딩 중...</div>
      </div>
    );
  }

  if (!product) {
    return (
      <div className="flex justify-center items-center min-h-[60vh]">
        <div className="text-lg text-gray-600">상품을 찾을 수 없습니다.</div>
      </div>
    );
  }

  const images = JSON.parse(product.images || '[]');
  const displayPrice = product.salePrice || product.price;
  const hasDiscount = product.salePrice && product.salePrice < product.price;

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <div>
          <div className="aspect-square bg-gray-100 rounded-lg overflow-hidden mb-4">
            <img
              src={images[selectedImage] || '/placeholder.png'}
              alt={product.name}
              className="w-full h-full object-cover"
            />
          </div>
          {images.length > 1 && (
            <div className="grid grid-cols-4 gap-2">
              {images.map((img: string, idx: number) => (
                <button
                  key={idx}
                  onClick={() => setSelectedImage(idx)}
                  className={`aspect-square bg-gray-100 rounded overflow-hidden ${
                    idx === selectedImage ? 'ring-2 ring-primary-600' : ''
                  }`}
                >
                  <img src={img} alt={`${product.name} ${idx + 1}`} className="w-full h-full object-cover" />
                </button>
              ))}
            </div>
          )}
        </div>

        <div>
          <h1 className="text-3xl font-bold text-gray-900 mb-4">{product.name}</h1>

          <div className="mb-6">
            {hasDiscount && (
              <div className="flex items-center gap-2 mb-2">
                <span className="text-2xl text-gray-500 line-through">
                  {product.price.toLocaleString()}원
                </span>
                <span className="text-xl font-semibold text-red-600">
                  {Math.round(((product.price - product.salePrice!) / product.price) * 100)}%
                </span>
              </div>
            )}
            <div className="text-4xl font-bold text-gray-900">
              {displayPrice.toLocaleString()}원
            </div>
          </div>

          <div className="border-t border-b py-6 mb-6 space-y-4">
            <div className="flex justify-between">
              <span className="text-gray-600">배송비</span>
              <span className="font-semibold">3,000원 (50,000원 이상 무료)</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">재고</span>
              <span className={product.stock > 0 ? 'text-green-600' : 'text-red-600'}>
                {product.stock > 0 ? `${product.stock}개` : '품절'}
              </span>
            </div>
          </div>

          {product.description && (
            <div className="mb-6">
              <h2 className="font-semibold text-lg mb-2">상품 설명</h2>
              <p className="text-gray-600 whitespace-pre-wrap">{product.description}</p>
            </div>
          )}

          <div className="flex items-center gap-4 mb-6">
            <label className="font-semibold">수량</label>
            <div className="flex items-center border rounded-lg">
              <button
                onClick={() => setQuantity(Math.max(1, quantity - 1))}
                className="px-4 py-2 hover:bg-gray-100"
              >
                -
              </button>
              <span className="px-4 py-2 border-x">{quantity}</span>
              <button
                onClick={() => setQuantity(Math.min(product.stock, quantity + 1))}
                className="px-4 py-2 hover:bg-gray-100"
                disabled={quantity >= product.stock}
              >
                +
              </button>
            </div>
          </div>

          <div className="space-y-3">
            <button
              onClick={handleAddToCart}
              disabled={product.stock === 0}
              className="w-full btn-primary"
            >
              {product.stock === 0 ? '품절' : '장바구니 담기'}
            </button>
            <button
              disabled={product.stock === 0}
              className="w-full bg-gray-800 text-white px-6 py-3 rounded-lg hover:bg-gray-900 transition-colors disabled:bg-gray-400"
            >
              바로 구매
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProductDetailPage;
