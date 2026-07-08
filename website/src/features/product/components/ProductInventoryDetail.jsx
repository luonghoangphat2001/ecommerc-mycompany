import React, { useState } from 'react';
import { Warehouse, CheckCircle, AlertCircle, MapPin, ChevronDown } from 'lucide-react';

const ProductInventoryDetail = ({ inventory, onSelectWarehouse }) => {
  const [isExpanded, setIsExpanded] = useState(false);
  
  if (!inventory || inventory.length === 0) return null;

  const totalStock = inventory.reduce((sum, inv) => sum + (inv.quantity || 0), 0);
  const availableWarehouses = inventory.filter(inv => inv.quantity > 0);
  const selectedWarehouse = inventory.find(inv => inv.quantity > 0);

  const handleSelectWarehouse = (warehouse) => {
    if (onSelectWarehouse) {
      onSelectWarehouse(warehouse);
    }
  };

  return (
    <div className="mt-6 p-4 bg-slate-50 rounded-xl border border-slate-200">
      <h3 className="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
        <Warehouse size={16} />
        Thông tin tồn kho
      </h3>
      
      <div className="space-y-3">
        {/* Summary */}
        <div className="flex justify-between text-sm">
          <span className="text-slate-600">Trạng thái:</span>
          <span className={`font-medium flex items-center gap-1 ${totalStock > 0 ? 'text-green-600' : 'text-red-500'}`}>
            {totalStock > 0 ? (
              <>
                <CheckCircle size={14} />
                Còn hàng
              </>
            ) : (
              <>
                <AlertCircle size={14} />
                Hết hàng
              </>
            )}
          </span>
        </div>

        {/* Warehouse Selector */}
        {availableWarehouses.length > 0 && (
          <div className="pt-2 border-t border-slate-200">
            <label className="text-sm font-medium text-slate-600 mb-2 block">
              Chọn kho giao hàng:
            </label>
            <div className="relative">
              <select 
                className="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                defaultValue={selectedWarehouse?.warehouse_id || ''}
                onChange={(e) => {
                  const warehouse = inventory.find(inv => inv.warehouse_id.toString() === e.target.value);
                  handleSelectWarehouse(warehouse);
                }}
              >
                {inventory.map((inv) => (
                  <option 
                    key={inv.warehouse_id} 
                    value={inv.warehouse_id}
                    disabled={inv.quantity === 0}
                  >
                    {inv.warehouse_name} - {inv.quantity > 0 ? `Còn ${inv.quantity}` : 'Hết hàng'}
                  </option>
                ))}
              </select>
            </div>
          </div>
        )}

        {/* Warehouse Details - Expandable */}
        <div className="pt-2 border-t border-slate-200">
          <button 
            onClick={() => setIsExpanded(!isExpanded)}
            className="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium"
          >
            <MapPin size={14} />
            Xem chi tiết {inventory.length} kho
            <ChevronDown 
              size={14} 
              className={`transform transition-transform ${isExpanded ? 'rotate-180' : ''}`} 
            />
          </button>
          
          {isExpanded && (
            <div className="mt-2 space-y-2">
              {inventory.map((inv) => (
                <div key={inv.warehouse_id} className="text-sm py-2 border-b border-slate-100 last:border-0">
                  <div className="flex justify-between items-center mb-1">
                    <span className="font-medium text-slate-700">{inv.warehouse_name}</span>
                    <span className={`text-xs font-medium ${inv.quantity > 0 ? 'text-green-600' : 'text-red-500'}`}>
                      {inv.quantity > 0 ? `Còn ${inv.quantity}` : 'Hết hàng'}
                    </span>
                  </div>
                  {inv.location && (
                    <div className="flex items-start gap-1 text-xs text-slate-500">
                      <MapPin size={12} className="mt-0.5 flex-shrink-0" />
                      <span>{inv.location}</span>
                    </div>
                  )}
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default ProductInventoryDetail;
