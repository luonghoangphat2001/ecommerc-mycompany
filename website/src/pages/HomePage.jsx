import React from "react"
import { Link } from "react-router-dom"
import { ArrowRight, Mail } from "lucide-react"
import { useProducts } from "../features/product/hooks/useProducts"
import ProductCard from "../features/product/components/ProductCard"
import useSettingsStore from "../store/useSettingsStore"
import Hero from "../features/home/components/Hero"
import Features from "../features/home/components/Features"
import { Button, PageHeading, ProductCardSkeleton } from "../components/common"

const HomePage = () => {
    const { data: productsData, isLoading } = useProducts()
    const allProducts = productsData?.data || []
    const featuredProducts = allProducts.filter((p) => p.is_featured).slice(0, 4)
    const displayProducts = featuredProducts.length > 0 ? featuredProducts : allProducts.slice(0, 4)
    const latestArrivals = allProducts.slice(0, 8)
    const translate = useSettingsStore((state) => state.translate)

    return (
        <div className="w-full">
            <Hero translate={translate} />

            <Features />

            {/* Featured Selection */}
            <section className="mb-14">
                <div className="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6 pb-4 border-b border-slate-200/80">
                    <div>
                        <PageHeading as="h2" size="md">
                            {translate("home.featured.title") || "Featured Products"}
                        </PageHeading>
                        <p className="text-sm text-slate-500 mt-1">
                            {translate("home.featured.subtitle") ||
                                "Curated high-demand products verified for immediate dispatch."}
                        </p>
                    </div>
                    <Link
                        to="/shop"
                        className="inline-flex items-center gap-1.5 text-sm font-semibold text-[#004687] hover:text-[#09253E] transition-colors group"
                    >
                        <span>{translate("home.featured.view_all") || "View Full Catalog"}</span>
                        <ArrowRight size={16} className="group-hover:translate-x-0.5 transition-transform" />
                    </Link>
                </div>

                {isLoading ? (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        {[...Array(4)].map((_, i) => (
                            <ProductCardSkeleton key={i} />
                        ))}
                    </div>
                ) : displayProducts.length > 0 ? (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        {displayProducts.map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>
                ) : null}
            </section>

            {/* Extended Catalog Arrivals (if more than 4 products exist) */}
            {latestArrivals.length > 4 && (
                <section className="mb-14">
                    <div className="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6 pb-4 border-b border-slate-200/80">
                        <div>
                            <PageHeading as="h2" size="md">
                                Latest Catalog Arrivals
                            </PageHeading>
                            <p className="text-sm text-slate-500 mt-1">
                                Newly stocked inventory across all distribution centers.
                            </p>
                        </div>
                        <Link
                            to="/shop"
                            className="inline-flex items-center gap-1.5 text-sm font-semibold text-[#004687] hover:text-[#09253E] transition-colors"
                        >
                            <span>Browse All ({allProducts.length})</span>
                            <ArrowRight size={16} />
                        </Link>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        {latestArrivals.slice(4, 8).map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>
                </section>
            )}

            {/* Structured Corporate Newsletter & Updates CTA */}
            <section className="bg-[#09253E] border border-slate-800 rounded-xl p-6 sm:p-10 mb-6 shadow-card">
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                    <div className="lg:col-span-7">
                        <h2 className="text-xl sm:text-2xl font-bold text-white tracking-tight mb-2">
                            {translate("home.cta.title") || "Stay Informed on New Inventory & Preferential Pricing"}
                        </h2>
                        <p className="text-slate-300 text-sm leading-relaxed max-w-xl">
                            {translate("home.cta.subtitle") ||
                                "Subscribe to receive product release bulletins, stock restock alerts, and seasonal corporate offers."}
                        </p>
                    </div>

                    <div className="lg:col-span-5">
                        <form
                            onSubmit={(e) => e.preventDefault()}
                            className="flex flex-col sm:flex-row gap-2.5"
                        >
                            <div className="relative flex-1">
                                <Mail
                                    size={16}
                                    className="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"
                                />
                                <input
                                    type="email"
                                    placeholder={translate("home.cta.placeholder") || "Enter your business or personal email..."}
                                    className="w-full bg-white/10 border border-white/15 rounded-lg pl-10 pr-3.5 py-2.5 text-sm text-white placeholder:text-slate-400 focus:outline-none focus:border-[#FBAE17] transition-colors"
                                />
                            </div>
                            <Button variant="gold" size="md" type="submit" className="shrink-0 px-5">
                                {translate("home.cta.button") || "Subscribe"}
                            </Button>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    )
}

export default HomePage
