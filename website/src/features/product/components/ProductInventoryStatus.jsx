import React from 'react';
import useSettingsStore from '../../../store/useSettingsStore';

const ProductInventoryStatus = ({ inventoryStatus }) => {
  const { translate, settings } = useSettingsStore();
  const manageStock = settings?.inventory?.manage_stock ?? true;

  if (!inventoryStatus || !manageStock) return null;

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
      <span className={textClass}>
        {inventoryStatus.message || translate(`product.${inventoryStatus.status}`) || inventoryStatus.status}
      </span>
    </div>
  );
};

export default ProductInventoryStatus;
