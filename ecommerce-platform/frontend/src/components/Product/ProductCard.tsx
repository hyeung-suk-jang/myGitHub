import { Link } from 'react-router-dom';
import { Product } from '../../types';

interface ProductCardProps {
  product: Product;
}

const ProductCard = ({ product }: ProductCardProps) => {
  const images = JSON.parse(product.images || '[]');
  const imageUrl = images[0] || '/placeholder.png';
  const displayPrice = product.salePrice || product.price;
  const hasDiscount = product.salePrice && product.salePrice < product.price;

  return (
    <Link to={`/products/${product.id}`} className="group">
      <div className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
        <div className="aspect-square overflow-hidden bg-gray-100">
          <img
            src={imageUrl}
            alt={product.name}
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          />
        </div>

        <div className="p-4">
          <h3 className="font-semibold text-gray-900 truncate group-hover:text-primary-600">
            {product.name}
          </h3>

          <div className="mt-2 flex items-center justify-between">
            <div>
              {hasDiscount && (
                <span className="text-sm text-gray-500 line-through mr-2">
                  {product.price.toLocaleString()}원
                </span>
              )}
              <span className="text-lg font-bold text-gray-900">
                {displayPrice.toLocaleString()}원
              </span>
            </div>

            {hasDiscount && (
              <span className="text-sm font-semibold text-red-600">
                {Math.round(((product.price - product.salePrice) / product.price) * 100)}%
              </span>
            )}
          </div>

          {product.stock === 0 && (
            <div className="mt-2">
              <span className="text-sm text-red-600">품절</span>
            </div>
          )}
        </div>
      </div>
    </Link>
  );
};

export default ProductCard;
