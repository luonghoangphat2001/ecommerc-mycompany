import React, { useState } from "react"
import { Warehouse, CheckCircle, AlertCircle, MapPin, ChevronDown } from "lucide-react"

const ProductInventoryDetail = ({ inventory, onSelectWarehouse }) => {
    const [isExpanded, setIsExpanded] = useState(false)

    if (!inventory || inventory.length === 0) return null

    const totalStock = inventory.reduce((sum, inv) => sum + (inv.quantity || 0), 0)
    const availableWarehouses = inventory.filter((inv) => inv.quantity > 0)
    const defaultWarehouse = inventory.find((inv) => inv.quantity > 0)

    const handleSelectWarehouse = (warehouse) => {
        if (onSelectWarehouse) {
            onSelectWarehouse(warehouse)
        }
    }

    return (
        <div className="mt-5 p-4 bg-slate-50/80 rounded-xl border border-slate-200/90 tabular-nums">
            <div className="flex items-center justify-between mb-3">
                <h3 className="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                    <Warehouse size={15} className="text-[#004687]" />
                    <span>Warehouse Availability</span>
                </h3>
                <span
                    className={`text-xs font-semibold inline-flex items-center gap-1 ${
                        totalStock > 0 ? "text-emerald-700" : "text-red-600"
                    }`}
                >
                    {totalStock > 0 ? (
                        <>
                            <CheckCircle size={13} />
                            <span>{totalStock} available</span>
                        </>
                    ) : (
                        <>
                            <AlertCircle size={13} />
                            <span>Depleted</span>
                        </>
                    )}
                </span>
            </div>

            <div className="space-y-3">
                {availableWarehouses.length > 0 && (
                    <div className="pt-2.5 border-t border-slate-200/80">
                        <label
                            htmlFor="warehouse-select"
                            className="text-xs font-semibold text-slate-600 mb-1.5 block"
                        >
                            Preferred Dispatch Hub:
                        </label>
                        <select
                            id="warehouse-select"
                            className="w-full px-3 py-2 text-xs font-medium border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-[#004687]/15 focus:border-[#004687] outline-none"
                            defaultValue={defaultWarehouse?.warehouse_id || ""}
                            onChange={(e) => {
                                const warehouse = inventory.find(
                                    (inv) => inv.warehouse_id.toString() === e.target.value
                                )
                                handleSelectWarehouse(warehouse)
                            }}
                        >
                            {inventory.map((inv) => (
                                <option
                                    key={inv.warehouse_id}
                                    value={inv.warehouse_id}
                                    disabled={inv.quantity === 0}
                                >
                                    {inv.warehouse_name} —{" "}
                                    {inv.quantity > 0 ? `${inv.quantity} in stock` : "Out of stock"}
                                </option>
                            ))}
                        </select>
                    </div>
                )}

                <div className="pt-2 border-t border-slate-200/80">
                    <button
                        type="button"
                        onClick={() => setIsExpanded(!isExpanded)}
                        className="flex items-center justify-between w-full text-xs text-[#004687] hover:text-[#09253E] font-semibold"
                    >
                        <span className="inline-flex items-center gap-1.5">
                            <MapPin size={13} />
                            <span>Inspect all {inventory.length} distribution hubs</span>
                        </span>
                        <ChevronDown
                            size={14}
                            className={`transform transition-transform duration-150 ${
                                isExpanded ? "rotate-180" : ""
                            }`}
                        />
                    </button>

                    {isExpanded && (
                        <div className="mt-2.5 space-y-1.5 pt-2 border-t border-slate-200/60">
                            {inventory.map((inv) => (
                                <div
                                    key={inv.warehouse_id}
                                    className="text-xs py-1.5 px-2.5 rounded-lg bg-white border border-slate-200/70"
                                >
                                    <div className="flex justify-between items-center">
                                        <span className="font-semibold text-slate-800">{inv.warehouse_name}</span>
                                        <span
                                            className={`font-semibold ${
                                                inv.quantity > 0 ? "text-emerald-700" : "text-red-600"
                                            }`}
                                        >
                                            {inv.quantity > 0 ? `${inv.quantity} units` : "0 units"}
                                        </span>
                                    </div>
                                    {inv.location && (
                                        <div className="flex items-start gap-1 text-[11px] text-slate-500 mt-0.5">
                                            <MapPin size={11} className="mt-0.5 flex-shrink-0" />
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
    )
}

export default ProductInventoryDetail
