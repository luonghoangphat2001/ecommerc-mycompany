import React from "react"
import { Link } from "react-router-dom"
import { Lock, Mail, ArrowLeft } from "lucide-react"

import useSettingsStore from "../../store/useSettingsStore"
import useLoginForm from "../../features/auth/hooks/useLoginForm"
import { FormInput, Button, Alert, Logo } from "../../components/common"

const LoginPage = () => {
    const translate = useSettingsStore((state) => state.translate)
    const { email, setEmail, password, setPassword, error, handleLogin } = useLoginForm()

    const forgotLink = (
        <Link
            to="/forgot-password"
            className="text-xs font-semibold text-[#004687] hover:text-[#09253E] transition-colors"
        >
            {translate("auth.forgot_password")}
        </Link>
    )

    return (
        <div className="min-h-screen bg-[#F8FAFC] flex flex-col items-center justify-center p-4 font-sans">
            <div className="w-full max-w-[420px] mb-6 flex items-center justify-between">
                <Logo />
                <Link
                    to="/"
                    className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#004687] transition-colors"
                >
                    <ArrowLeft size={14} />
                    <span>Storefront</span>
                </Link>
            </div>

            <div className="bg-white p-7 sm:p-9 w-full max-w-[420px] border border-slate-200/90 shadow-card rounded-xl">
                <div className="mb-6 pb-4 border-b border-slate-100">
                    <h1 className="text-xl font-bold text-[#09253E] tracking-tight">
                        {translate("auth.login_button") || "Sign In"}
                    </h1>
                    <p className="text-slate-500 text-xs mt-1">{translate("auth.login_subtitle")}</p>
                </div>

                {error && (
                    <Alert variant="error" shake className="mb-5">
                        {error}
                    </Alert>
                )}

                <form onSubmit={handleLogin} className="space-y-4">
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

                    <FormInput
                        label={translate("auth.password")}
                        name="password"
                        type="password"
                        icon={Lock}
                        placeholder="••••••••"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        labelExtra={forgotLink}
                        required
                    />

                    <Button type="submit" variant="primary" size="block" className="mt-2 h-11">
                        {translate("auth.login_button")}
                    </Button>
                </form>

                <div className="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500">
                    <span>{translate("auth.no_account")} </span>
                    <Link to="/register" className="font-bold text-[#004687] hover:underline ml-1">
                        {translate("auth.register_button")}
                    </Link>
                </div>
            </div>
        </div>
    )
}

export default LoginPage
