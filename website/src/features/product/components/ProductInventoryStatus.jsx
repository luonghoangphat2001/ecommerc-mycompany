import useSettingsStore from '../../../store/useSettingsStore';
import React from 'react';

const ProductInventoryStatus = ({ inventoryStatus }) => {
  const { translate } = useTranslation('product');

  if (!inventoryStatus) return null;

  const containerClass = "flex items-center gap-2";
  const dotClass = "w-2 h-2 rounded-full";
  const textClass = "text-sm";

  const statusColors = {
    in_stock: 'bg-green-500',
    low_stock: 'bg-yellow-500',
    out_of_stock: 'bg-red-500',
  };

  return (
    <div className={containerClass}>
      <span className={`${dotClass} ${statusColors[inventoryStatus.status] || 'bg-gray-500'}`} />
      <span className={textClass}>{inventoryStatus.message}</span>
    </div>
  );
};

export default ProductInventoryStatus;
