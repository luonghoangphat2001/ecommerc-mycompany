import React from "react"
import { ShoppingBag } from "lucide-react"

const CartIcon = ({ count, onClick }) => (
    <button
        type="button"
        onClick={onClick}
        aria-label="Shopping Cart"
        className="relative p-2 text-slate-700 hover:text-[#004687] transition-colors rounded-lg hover:bg-slate-100"
    >
        <ShoppingBag size={20} />
        {count > 0 && (
            <span className="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center text-[10px] font-bold leading-none text-white bg-[#004687] rounded-full tabular-nums shadow-card">
                {count}
            </span>
        )}
    </button>
)

export default CartIcon
