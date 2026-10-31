import React from "react"
import { Mail, ArrowLeft } from "lucide-react"
import { Link } from "react-router-dom"
import useSettingsStore from "../../store/useSettingsStore"
import useForgotPassword from "../../features/auth/hooks/useForgotPassword"
import { FormInput, Button, Alert, Logo } from "../../components/common"

const ForgotPasswordPage = () => {
    const translate = useSettingsStore((state) => state.translate)
    const { email, setEmail, status, error, isLoading, handleSubmit } = useForgotPassword()

    return (
        <div className="min-h-screen bg-[#F8FAFC] flex flex-col items-center justify-center p-4 font-sans">
            <div className="w-full max-w-[420px] mb-6 flex items-center justify-between">
                <Logo />
                <Link
                    to="/login"
                    className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#004687] transition-colors"
                >
                    <ArrowLeft size={14} />
                    <span>{translate("common.back_to_login") || "Back to Login"}</span>
                </Link>
            </div>

            <div className="bg-white p-7 sm:p-9 w-full max-w-[420px] border border-slate-200/90 shadow-card rounded-xl">
                <div className="mb-6 pb-4 border-b border-slate-100">
                    <h1 className="text-xl font-bold text-[#09253E] tracking-tight">
                        {translate("auth.forgot_password_title") || "Password Recovery"}
                    </h1>
                    <p className="text-slate-500 text-xs mt-1">
                        {translate("auth.forgot_password_subtitle") ||
                            "Enter your registered email address to receive reset instructions."}
                    </p>
                </div>

                {status && (
                    <Alert variant="success" className="mb-5">
                        {status}
                    </Alert>
                )}
                {error && (
                    <Alert variant="error" className="mb-5">
                        {error}
                    </Alert>
                )}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <FormInput
                        label={translate("auth.email")}
                        name="email"
                        type="email"
                        icon={Mail}
                        placeholder="email@example.com"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                    />

                    <Button type="submit" variant="primary" size="block" disabled={isLoading} className="mt-2 h-11">
                        {isLoading
                            ? translate("auth.processing")
                            : translate("auth.send_request") || "Send Reset Link"}
                    </Button>
                </form>
            </div>
        </div>
    )
}

export default ForgotPasswordPage
