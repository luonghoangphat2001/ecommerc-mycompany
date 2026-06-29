import React from "react"
import { ShoppingBag } from "lucide-react"

const CartIcon = ({ count, onClick }) => (
    <button onClick={onClick} className="relative p-2 text-slate-600 hover:text-blue-600 transition-colors rounded-full hover:bg-blue-50">
        <ShoppingBag size={22} />
        {count > 0 && <span className="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-blue-600 rounded-full">{count}</span>}
    </button>
)

export default CartIcon
