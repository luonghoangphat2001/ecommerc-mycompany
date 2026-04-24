import React from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight } from 'lucide-react';
import { useProducts } from '../features/product/hooks/useProducts';
import ProductCard from '../features/product/components/ProductCard';
import useSettingsStore from '../store/useSettingsStore';
import Hero from '../features/home/components/Hero';
import Features from '../features/home/components/Features';

const HomePage = () => {
  const { data: productsData } = useProducts();
  const featuredProducts = productsData?.data?.filter(p => p.is_featured).slice(0, 4) || [];
  const t = useSettingsStore((state) => state.t);

  return (
    <div className="w-full -mt-8">
      <Hero t={t} />
      
      <Features />

      {/* Featured Products */}
      {featuredProducts.length > 0 && (
        <section className="mb-24">
          <div className="flex items-end justify-between mb-10">
            <div>
              <h2 className="text-3xl font-black text-slate-900 tracking-tight mb-2">Sản phẩm nổi bật</h2>
              <p className="text-slate-500 font-medium">Những lựa chọn tốt nhất dành cho bạn tuần này.</p>
            </div>
            <Link to="/shop" className="text-blue-600 font-bold flex items-center gap-2 hover:gap-3 transition-all">
              Tất cả sản phẩm <ArrowRight size={18} />
            </Link>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            {featuredProducts.map(product => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        </section>
      )}

      {/* CTA Section */}
      <section className="bg-slate-900 rounded-[3rem] p-12 md:p-20 text-center relative overflow-hidden mb-12">
        <div className="absolute top-0 right-0 w-[400px] h-[400px] bg-blue-500 rounded-full blur-[100px] opacity-20 -mr-20 -mt-20"></div>
        <div className="relative z-10">
          <h2 className="text-3xl md:text-5xl font-black text-white mb-6 tracking-tight">Sẵn sàng nâng cấp phong cách của bạn?</h2>
          <p className="text-slate-400 text-lg mb-10 max-w-2xl mx-auto">
            Đăng ký nhận bản tin để nhận ưu đãi 10% cho đơn hàng đầu tiên và cập nhật những bộ sưu tập mới nhất.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
            <input 
              type="email" 
              placeholder="Nhập email của bạn..." 
              className="flex-1 bg-white/10 border border-white/20 rounded-2xl px-6 py-4 text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all"
            />
            <button className="bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-4 rounded-2xl transition-all shadow-xl shadow-blue-900/20">
              Đăng ký
            </button>
          </div>
        </div>
      </section>
    </div>
  );
};

export default HomePage;


