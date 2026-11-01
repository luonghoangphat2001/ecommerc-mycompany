import { useState, useMemo, useEffect } from 'react';
import { useQuery } from '@tanstack/react-query';
import productApi from '../services/productApi';
import useCartStore from '../../cart/store/useCartStore';
import toast from 'react-hot-toast';

const useProductDetail = (product) => {
  const addToCart = useCartStore((state) => state.addToCart);
  const [quantity, setQuantity] = useState(1);
  const [selectedAttributes, setSelectedAttributes] = useState({});
  const [selectedVariant, setSelectedVariant] = useState(null);
  const [selectedWarehouse, setSelectedWarehouse] = useState(null);

  const productId = product?.id;
  const isVariable = product?.product_type === 'variable' || (product?.variants && product.variants.length > 0);
  const isCombo = product?.product_type === 'combo' || (product?.combo_components && product.combo_components.length > 0);

  // Initialize default attributes for variable products
  useEffect(() => {
    if (isVariable && product?.attributes && product.attributes.length > 0) {
      const initialAttrs = {};
      product.attributes.forEach((attr) => {
        if (attr.options && attr.options.length > 0) {
          initialAttrs[attr.name] = attr.options[0];
        }
      });
      setSelectedAttributes(initialAttrs);
    }
  }, [product?.id, isVariable, product?.attributes]);

  // Match selectedVariant when selectedAttributes change
  useEffect(() => {
    if (isVariable && product?.variants && product.variants.length > 0) {
      const matched = product.variants.find((v) => {
        if (!v.attributes) return false;
        return Object.entries(selectedAttributes).every(([attrName, attrVal]) => {
          return String(v.attributes[attrName]) === String(attrVal);
        });
      });
      setSelectedVariant(matched || null);
    } else {
      setSelectedVariant(null);
    }
  }, [selectedAttributes, product?.variants, isVariable]);

  // Handle attribute change from UI
  const handleAttributeChange = (attributeName, optionValue) => {
    setSelectedAttributes((prev) => ({
      ...prev,
      [attributeName]: optionValue,
    }));
  };

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

  // Calculate stock based on product type
  const stock = useMemo(() => {
    if (isVariable) {
      if (selectedVariant) {
        return selectedVariant.stock ?? selectedVariant.qty ?? 0;
      }
      return (product?.variants || []).reduce((sum, v) => sum + (v.stock ?? v.qty ?? 0), 0);
    }

    if (isCombo) {
      return product?.stock ?? product?.qty ?? 0;
    }

    // Simple product stock check
    const inventoryArray = inventoryData?.data || [];
    if (inventoryArray.length > 0) {
      return inventoryArray.reduce((sum, inv) => sum + (inv.quantity || 0), 0);
    }
    if (product?.qty !== undefined) {
      return product.qty;
    }
    return product?.stock || product?.quantity || 0;
  }, [inventoryData, product, isVariable, isCombo, selectedVariant]);

  const isAvailable = stock > 0 && product?.is_available !== false;
  const isLowStock = stock > 0 && stock <= 5;
  const maxQuantity = Math.max(1, Math.min(stock, 99));

  // Price calculation
  const currentPrice = useMemo(() => {
    if (isVariable && selectedVariant) {
      return selectedVariant.effective_price || selectedVariant.price || 0;
    }
    return product?.effective_price || product?.price || 0;
  }, [selectedVariant, product, isVariable]);

  const oldPrice = useMemo(() => {
    if (isVariable && selectedVariant) {
      return selectedVariant.sale_price && selectedVariant.sale_price < selectedVariant.price
        ? selectedVariant.price
        : null;
    }
    return product?.is_on_sale ? product?.price : product?.old_price || null;
  }, [selectedVariant, product, isVariable]);

  const discountPercent = useMemo(() => {
    if (oldPrice && oldPrice > currentPrice) {
      return Math.round((1 - currentPrice / oldPrice) * 100);
    }
    return 0;
  }, [oldPrice, currentPrice]);

  // Handle add to cart
  const handleAddToCart = () => {
    if (!product) {
      toast.error('Lỗi: Không tìm thấy thông tin sản phẩm');
      return;
    }

    if (isVariable && !selectedVariant) {
      toast.error('Vui lòng chọn đầy đủ các thuộc tính của sản phẩm');
      return;
    }

    if (stock <= 0) {
      toast.error('Sản phẩm hoặc biến thể đã hết hàng');
      return;
    }

    try {
      const itemToAdd = {
        ...product,
        id: product.id,
        price: currentPrice,
        old_price: oldPrice,
        variant: selectedVariant,
        variant_id: selectedVariant?.id || null,
        attributes: selectedVariant?.attributes || null,
        stock,
        selectedWarehouse,
      };

      addToCart(itemToAdd, quantity);
      useCartStore.getState().openCart();

      const variantInfo = selectedVariant?.attributes ? ` (${Object.values(selectedVariant.attributes).join(', ')})` : '';
      toast.success(`Đã thêm "${product.name}${variantInfo}" vào giỏ hàng`, {
        duration: 3000,
        icon: '🛒',
      });
    } catch (error) {
      console.error('Error adding to cart:', error);
      toast.error('Có lỗi xảy ra khi thêm vào giỏ hàng');
    }
  };

  // Quantity controls
  const incrementQuantity = () => setQuantity((prev) => Math.min(prev + 1, maxQuantity));
  const decrementQuantity = () => setQuantity((prev) => Math.max(1, prev - 1));

  // Images gallery
  const images = useMemo(() => {
    const variantImage = selectedVariant?.image;
    const mainImage = variantImage || product?.image?.url || product?.image || product?.image_url;
    const galleryImages = product?.product_images || product?.gallery || [];
    return mainImage ? [mainImage, ...galleryImages] : galleryImages;
  }, [product, selectedVariant]);

  return {
    product,
    isVariable,
    isCombo,
    isLoading: false,
    isError: false,
    error: null,

    // Attributes & Variants
    attributes: product?.attributes || [],
    selectedAttributes,
    handleAttributeChange,
    selectedVariant,
    setSelectedVariant,
    variants: product?.variants || [],
    comboComponents: product?.combo_components || [],

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
    description: product?.description,
    sku: selectedVariant?.sku || product?.sku || '',
  };
};

export default useProductDetail;
