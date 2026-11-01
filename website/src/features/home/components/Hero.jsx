import React from "react"
import { Link } from "react-router-dom"
import { ArrowRight, ShieldCheck, Truck, Layers, CheckCircle2 } from "lucide-react"
import { Button } from "../../../components/common"

const Hero = ({ translate }) => {
    return (
        <section className="relative overflow-hidden rounded-xl border border-slate-200/90 bg-white mb-10 shadow-card">
            <div className="grid grid-cols-1 lg:grid-cols-12">
                {/* Left Column: Value Proposition & Primary Actions */}
                <div className="lg:col-span-7 p-6 sm:p-10 lg:p-12 flex flex-col justify-between">
                    <div>
                        <div className="inline-flex items-center gap-2 text-xs font-semibold text-[#004687] mb-4">
                            <span className="w-2 h-2 rounded-full bg-[#FBAE17]" />
                            <span>
                                {translate?.("home.hero.network_label") || "VERIFIED RETAIL & FULFILLMENT NETWORK"}
                            </span>
                        </div>

                        <h1 className="text-2xl sm:text-4xl lg:text-[40px] font-extrabold text-[#09253E] mb-4 leading-[1.15] tracking-tight">
                            {translate?.("home.hero.title_1") || "Precision Commerce"} &{" "}
                            <span className="text-[#004687]">
                                {translate?.("home.hero.title_2") || "Curated Essentials"}
                            </span>
                        </h1>

                        <p className="text-sm sm:text-base text-slate-600 mb-7 max-w-xl leading-relaxed">
                            {translate?.("home.hero.subtitle") ||
                                "Discover quality-assured electronics, workspace instruments, and modern lifestyle goods backed by real-time multi-warehouse inventory and rapid nationwide dispatch."}
                        </p>

                        <div className="flex flex-wrap items-center gap-3 mb-8">
                            <Button
                                as={Link}
                                to="/shop"
                                variant="primary"
                                size="lg"
                                className="gap-2 group"
                            >
                                <span>
                                    {translate?.("home.hero.cta_primary") ||
                                        translate?.("header.shop") ||
                                        "Browse Catalog"}
                                </span>
                                <ArrowRight size={16} className="group-hover:translate-x-0.5 transition-transform" />
                            </Button>
                            <Link
                                to="/posts"
                                className="px-5 py-2.5 bg-white border border-slate-300 text-slate-800 font-semibold rounded-lg hover:bg-slate-50 hover:border-slate-400 transition-colors text-sm"
                            >
                                {translate?.("header.blog") || "Editorial & Guides"}
                            </Link>
                        </div>
                    </div>

                    {/* Structured Key Service Metrics */}
                    <div className="pt-6 border-t border-slate-200/80 grid grid-cols-3 gap-4 tabular-nums">
                        <div>
                            <div className="text-lg sm:text-xl font-extrabold text-[#09253E]">100%</div>
                            <div className="text-xs text-slate-500 font-medium">
                                {translate?.("home.hero.metric_origin") || "Origin Verified"}
                            </div>
                        </div>
                        <div className="border-l border-slate-200/80 pl-4">
                            <div className="text-lg sm:text-xl font-extrabold text-[#004687]">24h</div>
                            <div className="text-xs text-slate-500 font-medium">
                                {translate?.("home.hero.metric_dispatch") || "Order Dispatch SLA"}
                            </div>
                        </div>
                        <div className="border-l border-slate-200/80 pl-4">
                            <div className="text-lg sm:text-xl font-extrabold text-[#09253E]">30-Day</div>
                            <div className="text-xs text-slate-500 font-medium">
                                {translate?.("home.hero.metric_returns") || "Protected Returns"}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Right Column: Structured Enterprise Fulfillment & Showcase Panel */}
                <div className="lg:col-span-5 bg-[#09253E] text-white p-6 sm:p-10 flex flex-col justify-between relative overflow-hidden border-t lg:border-t-0 lg:border-l border-slate-200/20">
                    <div
                        aria-hidden="true"
                        className="pointer-events-none absolute inset-0 opacity-15"
                        style={{
                            backgroundImage: "radial-gradient(rgba(255, 255, 255, 0.35) 1px, transparent 1px)",
                            backgroundSize: "20px 20px",
                        }}
                    />

                    <div className="relative z-10">
                        <div className="flex items-center justify-between pb-4 mb-5 border-b border-white/10">
                            <span className="text-xs font-semibold uppercase tracking-wider text-slate-300">
                                {translate?.("home.hero.live_inventory") || "Live Inventory & Fulfillment"}
                            </span>
                            <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-400/20">
                                <span className="w-1.5 h-1.5 rounded-full bg-emerald-400" />
                                {translate?.("home.hero.operational") || "Operational"}
                            </span>
                        </div>

                        <div className="space-y-3">
                            <div className="p-3.5 rounded-lg bg-white/[0.06] border border-white/10 flex items-start gap-3">
                                <Layers size={18} className="text-[#FBAE17] mt-0.5 flex-shrink-0" />
                                <div>
                                    <div className="text-xs font-bold text-white">
                                        {translate?.("home.hero.feature_1_title") ||
                                            "Multi-Warehouse Stock Visibility"}
                                    </div>
                                    <p className="text-xs text-slate-300 mt-0.5 leading-relaxed">
                                        {translate?.("home.hero.feature_1_desc") ||
                                            "Check exact unit availability by distribution hub before placing your order."}
                                    </p>
                                </div>
                            </div>

                            <div className="p-3.5 rounded-lg bg-white/[0.06] border border-white/10 flex items-start gap-3">
                                <Truck size={18} className="text-[#FBAE17] mt-0.5 flex-shrink-0" />
                                <div>
                                    <div className="text-xs font-bold text-white">
                                        {translate?.("home.hero.feature_2_title") || "End-to-End Order Tracking"}
                                    </div>
                                    <p className="text-xs text-slate-300 mt-0.5 leading-relaxed">
                                        {translate?.("home.hero.feature_2_desc") ||
                                            "Real-time status updates from warehouse packing to doorstep handover."}
                                    </p>
                                </div>
                            </div>

                            <div className="p-3.5 rounded-lg bg-white/[0.06] border border-white/10 flex items-start gap-3">
                                <ShieldCheck size={18} className="text-[#FBAE17] mt-0.5 flex-shrink-0" />
                                <div>
                                    <div className="text-xs font-bold text-white">
                                        {translate?.("home.hero.feature_3_title") || "Official Warranty & Invoice"}
                                    </div>
                                    <p className="text-xs text-slate-300 mt-0.5 leading-relaxed">
                                        {translate?.("home.hero.feature_3_desc") ||
                                            "Every purchase includes digital invoice documentation and manufacturer coverage."}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="relative z-10 pt-5 mt-6 border-t border-white/10 flex items-center justify-between text-xs text-slate-300">
                        <span className="inline-flex items-center gap-1.5">
                            <CheckCircle2 size={14} className="text-[#FBAE17]" />
                            <span>
                                {translate?.("home.hero.free_shipping_note") ||
                                    "Free shipping on orders over 500,000₫"}
                            </span>
                        </span>
                        <Link to="/shop" className="text-[#FBAE17] hover:underline font-semibold">
                            {translate?.("home.hero.view_all") || "View All →"}
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    )
}

export default Hero
