import { useState } from 'react';
import { useParams } from 'react-router-dom';
import { useProduct } from '../hooks/useProducts';
import useCartStore from '../../cart/store/useCartStore';

const useProductDetail = () => {
  const { id } = useParams();
  const { data: productData, isLoading, isError, error } = useProduct(id);
  const addToCart = useCartStore((state) => state.addToCart);
  const [quantity, setQuantity] = useState(1);

  const handleAddToCart = () => {
    if (productData?.data) {
      addToCart(productData.data, quantity);
    }
  };

  const incrementQuantity = () => setQuantity(prev => prev + 1);
  const decrementQuantity = () => setQuantity(prev => Math.max(1, prev - 1));

  return {
    product: productData?.data,
    isLoading,
    isError,
    error,
    quantity,
    handleAddToCart,
    incrementQuantity,
    decrementQuantity
  };
};

export default useProductDetail;
