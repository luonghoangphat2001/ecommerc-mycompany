import React from "react"
import { Mail, ArrowLeft } from "lucide-react"
import { Link } from "react-router-dom"
import useSettingsStore from "../../store/useSettingsStore"
import useForgotPassword from "../../features/auth/hooks/useForgotPassword"
import { FormInput, Button, Alert, BackgroundOrbs } from "../../components/common"

const ForgotPasswordPage = () => {
    const translate = useSettingsStore((state) => state.translate)
    const { email, setEmail, status, error, isLoading, handleSubmit } = useForgotPassword()

    return (
        <div className="min-h-screen bg-slate-50 flex items-center justify-center relative overflow-hidden font-sans">
            <BackgroundOrbs preset="auth" />

            <div className="bg-white/60 backdrop-blur-xl p-8 md:p-12 w-full max-w-[450px] border border-white/60 shadow-[0_20px_50px_rgba(0,0,0,0.05)] rounded-[2.5rem] relative z-10 mx-4">
                <Link to="/login" className="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-blue-600 transition-colors mb-8">
                    <ArrowLeft size={16} />
                    {translate("common.back_to_login") || "Quay lại đăng nhập"}
                </Link>

                <div className="text-center mb-10">
                    <h1 className="text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 mb-3 tracking-tight">{translate("auth.forgot_password_title") || "Quên mật khẩu?"}</h1>
                    <p className="text-slate-500 font-medium">{translate("auth.forgot_password_subtitle") || "Nhập email của bạn để nhận hướng dẫn đặt lại mật khẩu"}</p>
                </div>

                {status && (
                    <Alert variant="success" className="mb-6">
                        {status}
                    </Alert>
                )}
                {error && (
                    <Alert variant="error" className="mb-6">
                        {error}
                    </Alert>
                )}

                <form onSubmit={handleSubmit} className="space-y-6">
                    <FormInput label={translate("auth.email")} type="email" icon={Mail} placeholder="email@example.com" value={email} onChange={(e) => setEmail(e.target.value)} required />

                    <Button type="submit" size="block" disabled={isLoading} className="mt-4">
                        {isLoading ? translate("auth.processing") : translate("auth.send_request") || "Gửi yêu cầu"}
                    </Button>
                </form>
            </div>
        </div>
    )
}

export default ForgotPasswordPage
