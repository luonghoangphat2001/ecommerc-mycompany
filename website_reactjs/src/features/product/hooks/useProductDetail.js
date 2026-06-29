import { useState, useMemo } from 'react';
import { useQuery } from '@tanstack/react-query';
import productApi from '../services/productApi';
import useCartStore from '../../cart/store/useCartStore';
import toast from 'react-hot-toast';

const useProductDetail = (product) => {
  const addToCart = useCartStore((state) => state.addToCart);
  const [quantity, setQuantity] = useState(1);
  const [selectedVariant, setSelectedVariant] = useState(null);
  const [selectedWarehouse, setSelectedWarehouse] = useState(null);

  const productId = product?.id;

  // Fetch inventory from API
  const { data: inventoryData } = useQuery({
    queryKey: ['product-inventory', productId, selectedVariant?.id],
    queryFn: () => productApi.getInventory(productId, selectedVariant?.id),
    enabled: !!productId,
    staleTime: 1000 * 60,
  });

  // Fetch upsell products
  const { data: upsellsData } = useQuery({
    queryKey: ['product-upsells', productId],
    queryFn: () => productApi.getUpsellProducts(productId),
    enabled: !!productId && !!product,
    staleTime: 1000 * 60 * 5,
    retry: false,
  });

  // Fetch cross-sell products
  const { data: crossSellsData } = useQuery({
    queryKey: ['product-cross-sells', productId],
    queryFn: () => productApi.getCrossSellProducts(productId),
    enabled: !!productId && !!product,
    staleTime: 1000 * 60 * 5,
    retry: false,
  });

  // Calculate stock from inventory API data or product data
  const stock = useMemo(() => {
    // First try inventory API data
    const inventoryArray = inventoryData?.data || [];
    if (inventoryArray.length > 0) {
      const total = inventoryArray.reduce((sum, inv) => sum + (inv.quantity || 0), 0);
      console.log('Stock from inventory API:', total);
      return total;
    }
    // Fallback to product.qty (direct from product data)
    if (product?.qty) {
      console.log('Stock from product.qty:', product.qty);
      return product.qty;
    }
    // Try from product.inventories
    if (product?.inventories && Array.isArray(product.inventories)) {
      const total = product.inventories.reduce((sum, inv) => {
        return sum + (inv.pivot?.stock_quantity || inv.stock_quantity || 0);
      }, 0);
      console.log('Stock from product.inventories:', total);
      return total;
    }
    // Last fallback
    const fallbackStock = product?.stock || product?.quantity || 0;
    console.log('Stock from fallback:', fallbackStock);
    return fallbackStock;
  }, [inventoryData, product]);

  const isAvailable = stock > 0 && product?.is_available !== false;
  const isLowStock = stock > 0 && stock <= 5;
  const maxQuantity = Math.min(stock, 99);

  // Price calculation
  const currentPrice = useMemo(() => {
    if (selectedVariant?.price) return selectedVariant.price;
    return product?.price || 0;
  }, [selectedVariant, product]);

  const oldPrice = useMemo(() => {
    if (selectedVariant?.old_price) return selectedVariant.old_price;
    return product?.old_price || 0;
  }, [selectedVariant, product]);

  const discountPercent = useMemo(() => {
    if (oldPrice && oldPrice > currentPrice) {
      return Math.round((1 - currentPrice / oldPrice) * 100);
    }
    return 0;
  }, [oldPrice, currentPrice]);

  // Handle add to cart
  const handleAddToCart = () => {
    console.log('handleAddToCart called', { 
      hasProduct: !!product, 
      productId: product?.id,
      productName: product?.name,
      isAvailable, 
      stock, 
      quantity,
      selectedWarehouse 
    });
    
    if (!product) {
      toast.error('Lỗi: Không tìm thấy thông tin sản phẩm');
      return;
    }
    
    if (stock <= 0) {
      toast.error('Sản phẩm đã hết hàng');
      return;
    }
    
    if (isAvailable) {
      try {
        const itemToAdd = {
          ...product,
          price: currentPrice,
          old_price: oldPrice,
          variant: selectedVariant,
          stock,
          selectedWarehouse,
        };
        console.log('Adding to cart:', itemToAdd);
        addToCart(itemToAdd, quantity);
        useCartStore.getState().openCart();
        
        const warehouseName = selectedWarehouse?.warehouse_name || 'mặc định';
        toast.success(`Đã thêm "${product.name}" vào giỏ hàng (${warehouseName})`, {
          duration: 3000,
          icon: '🛒',
        });
      } catch (error) {
        console.error('Error adding to cart:', error);
        toast.error('Có lỗi xảy ra khi thêm vào giỏ hàng');
      }
    } else {
      console.log('Cannot add to cart:', { product, isAvailable, stock });
      toast.error('Không thể thêm sản phẩm vào giỏ hàng (hết hàng hoặc không khả dụng)');
    }
  };

  // Quantity controls
  const incrementQuantity = () => setQuantity(prev => Math.min(prev + 1, maxQuantity));
  const decrementQuantity = () => setQuantity(prev => Math.max(1, prev - 1));

  // Images gallery
  const images = useMemo(() => {
    const mainImage = product?.image?.url || product?.image || product?.image_url;
    const galleryImages = product?.product_images || product?.gallery || [];
    return mainImage ? [mainImage, ...galleryImages] : galleryImages;
  }, [product]);

  return {
    // Product data
    product,
    isLoading: false,
    isError: false,
    error: null,
    
    // Variants & Selection
    selectedVariant,
    setSelectedVariant,
    variants: product?.variants || [],
    
    // Inventory
    stock,
    isAvailable,
    isLowStock,
    inventory: inventoryData?.data || [],
    selectedWarehouse,
    setSelectedWarehouse,
    
    // Pricing
    currentPrice,
    oldPrice,
    discountPercent,
    
    // Quantity
    quantity,
    maxQuantity,
    setQuantity,
    incrementQuantity,
    decrementQuantity,
    handleAddToCart,
    
    // Related products
    upsells: upsellsData?.data || [],
    crossSells: crossSellsData?.data || [],
    
    // Gallery
    images,
    
    // Additional info
    brand: product?.brand,
    categories: product?.categories || [],
    attributes: product?.attributes || [],
    description: product?.description,
    specifications: product?.specifications || [],
    reviews: product?.reviews || [],
    rating: product?.rating || product?.avg_rating || 0,
    reviewCount: product?.review_count || 0,
    isNew: product?.is_new || false,
    isFeatured: product?.featured || product?.is_featured || false,
    sku: product?.sku || '',
    barcode: product?.barcode || '',
  };
};

export default useProductDetail;
