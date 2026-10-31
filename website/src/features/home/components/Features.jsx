import React from "react"
import { Zap, ShieldCheck, Truck, RefreshCw } from "lucide-react"

const ITEMS = [
    {
        icon: Zap,
        title: "Express 24h Dispatch",
        desc: "Same-day warehouse processing and rapid metro delivery.",
        accent: "bg-[#004687]/10 text-[#004687]",
    },
    {
        icon: ShieldCheck,
        title: "Authorized Warranty",
        desc: "100% genuine merchandise with full manufacturer coverage.",
        accent: "bg-amber-50 text-amber-700 border border-amber-200/60",
    },
    {
        icon: Truck,
        title: "Free Nationwide Shipping",
        desc: "Complimentary standard delivery on orders from 500,000₫.",
        accent: "bg-[#004687]/10 text-[#004687]",
    },
    {
        icon: RefreshCw,
        title: "Transparent Returns",
        desc: "Streamlined 30-day exchange and refund resolution.",
        accent: "bg-emerald-50 text-emerald-700 border border-emerald-200/60",
    },
]

const Features = () => {
    return (
        <section className="border border-slate-200/90 bg-white rounded-xl p-5 sm:p-6 mb-12 shadow-card">
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                {ITEMS.map((item, idx) => {
                    const Icon = item.icon
                    const padClass =
                        idx === 0
                            ? "pb-4 sm:pb-0 sm:pr-5"
                            : idx === ITEMS.length - 1
                            ? "pt-4 sm:pt-0 sm:pl-5"
                            : "py-4 sm:py-0 sm:px-5"
                    return (
                        <div key={item.title} className={`flex items-start gap-3.5 ${padClass}`}>
                            <div
                                className={`w-10 h-10 rounded-lg flex items-center justify-center shrink-0 ${item.accent}`}
                            >
                                <Icon size={19} strokeWidth={2} />
                            </div>
                            <div className="min-w-0">
                                <h3 className="font-bold text-[#09253E] text-sm leading-snug mb-0.5">{item.title}</h3>
                                <p className="text-xs text-slate-500 leading-relaxed">{item.desc}</p>
                            </div>
                        </div>
                    )
                })}
            </div>
        </section>
    )
}

export default Features
