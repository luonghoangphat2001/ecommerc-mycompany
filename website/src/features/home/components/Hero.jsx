import React from "react"
import { Link } from "react-router-dom"
import { ArrowRight } from "lucide-react"
import { Button, BackgroundOrbs } from "../../../components/common"

const Hero = ({ translate }) => {
    return (
        <section className="relative min-h-[80vh] flex items-center justify-center overflow-hidden rounded-[3rem] mb-20">
            <div className="absolute inset-0 bg-gradient-to-br from-blue-600/20 via-purple-500/10 to-transparent z-0"></div>
            <BackgroundOrbs preset="hero" />

            <div className="max-w-4xl mx-auto text-center relative z-10 px-4">
                <span className="inline-block px-4 py-1.5 bg-blue-100 text-blue-600 rounded-full text-sm font-bold mb-6 animate-in slide-in-from-bottom duration-500">{translate("product.featured")} 2026 Collection</span>
                <h1 className="text-5xl md:text-7xl font-black text-slate-900 mb-8 leading-[1.1] tracking-tight animate-in slide-in-from-bottom duration-700 delay-100">
                    Trải Nghiệm Mua Sắm <br />
                    <span className="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600">Đẳng Cấp NovaStore</span>
                </h1>
                <p className="text-xl text-slate-600 mb-10 max-w-2xl mx-auto font-medium leading-relaxed animate-in slide-in-from-bottom duration-700 delay-200">Khám phá bộ sưu tập sản phẩm công nghệ và phong cách sống cao cấp, mang lại giá trị thực thụ cho phong cách của bạn.</p>

                <div className="flex flex-col sm:flex-row items-center justify-center gap-4 animate-in slide-in-from-bottom duration-700 delay-300">
                    <Button as={Link} to="/shop" size="xl" className="gap-2 group hover:-translate-y-1">
                        {translate("header.shop")} Ngay
                        <ArrowRight size={20} className="group-hover:translate-x-1 transition-transform" />
                    </Button>
                    <Link to="/docs/api" className="px-10 py-4 bg-white/60 backdrop-blur-xl border border-white/60 text-slate-800 font-bold rounded-2xl hover:bg-white transition-all shadow-lg transform hover:-translate-y-1">
                        Tìm hiểu thêm
                    </Link>
                </div>
            </div>
        </section>
    )
}

export default Hero
