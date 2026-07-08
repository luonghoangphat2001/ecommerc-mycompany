import React from "react"
import { Zap, ShieldCheck, Truck } from "lucide-react"

const Features = () => {
    return (
        <section className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-24">
            <div className="bg-white/40 backdrop-blur-md p-8 rounded-[2rem] border border-white/60 shadow-[0_8px_30px_rgba(0,0,0,0.02)] flex items-start gap-5">
                <div className="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center shrink-0">
                    <Zap size={24} />
                </div>
                <div>
                    <h3 className="font-bold text-slate-800 mb-1">Giao hàng siêu tốc</h3>
                    <p className="text-sm text-slate-500">Nhận hàng chỉ trong vòng 24h tại khu vực nội thành.</p>
                </div>
            </div>
            <div className="bg-white/40 backdrop-blur-md p-8 rounded-[2rem] border border-white/60 shadow-[0_8px_30px_rgba(0,0,0,0.02)] flex items-start gap-5">
                <div className="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center shrink-0">
                    <ShieldCheck size={24} />
                </div>
                <div>
                    <h3 className="font-bold text-slate-800 mb-1">Bảo hành chính hãng</h3>
                    <p className="text-sm text-slate-500">Cam kết 100% sản phẩm chính hãng với chế độ bảo hành ưu việt.</p>
                </div>
            </div>
            <div className="bg-white/40 backdrop-blur-md p-8 rounded-[2rem] border border-white/60 shadow-[0_8px_30px_rgba(0,0,0,0.02)] flex items-start gap-5">
                <div className="w-12 h-12 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center shrink-0">
                    <Truck size={24} />
                </div>
                <div>
                    <h3 className="font-bold text-slate-800 mb-1">Miễn phí vận chuyển</h3>
                    <p className="text-sm text-slate-500">Miễn phí toàn quốc cho đơn hàng từ 500.000 VNĐ.</p>
                </div>
            </div>
        </section>
    )
}

export default Features
