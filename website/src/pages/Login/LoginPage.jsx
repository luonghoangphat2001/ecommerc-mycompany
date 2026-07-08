import React from "react"
import { Link } from "react-router-dom"
import { Lock, Mail } from "lucide-react"

import useSettingsStore from "../../store/useSettingsStore"
import useLoginForm from "../../features/auth/hooks/useLoginForm"
import { FormInput, Button, Alert, BackgroundOrbs } from "../../components/common"

const LoginPage = () => {
    const translate = useSettingsStore((state) => state.translate)
    const { email, setEmail, password, setPassword, error, handleLogin } = useLoginForm()

    const forgotLink = (
        <Link to="/forgot-password" className="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
            {translate("auth.forgot_password")}
        </Link>
    )

    return (
        <div className="min-h-screen bg-slate-50 flex items-center justify-center relative overflow-hidden font-sans">
            <BackgroundOrbs preset="auth" />

            <div className="bg-white/60 backdrop-blur-xl p-8 md:p-12 w-full max-w-[450px] border border-white/60 shadow-[0_20px_50px_rgba(0,0,0,0.05)] rounded-[2.5rem] relative z-10 mx-4">
                <div className="text-center mb-10">
                    <h1 className="text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 mb-3 tracking-tight">NovaStore</h1>
                    <p className="text-slate-500 font-medium">{translate("auth.login_subtitle")}</p>
                </div>

                {error && (
                    <Alert variant="error" shake className="mb-6 flex items-center">
                        {error}
                    </Alert>
                )}

                <form onSubmit={handleLogin} className="space-y-6">
                    <FormInput label={translate("auth.email")} type="email" icon={Mail} placeholder="email@example.com" value={email} onChange={(e) => setEmail(e.target.value)} required />

                    <FormInput label={translate("auth.password")} type="password" icon={Lock} placeholder="••••••••" value={password} onChange={(e) => setPassword(e.target.value)} labelExtra={forgotLink} required />

                    <Button type="submit" size="block" className="mt-4">
                        {translate("auth.login_button")}
                    </Button>
                </form>

                <div className="mt-10 text-center text-sm">
                    <span className="text-slate-500">{translate("auth.no_account")} </span>
                    <Link to="/register" className="font-bold text-blue-600 hover:text-blue-700 transition-colors ml-1">
                        {translate("auth.register_button")}
                    </Link>
                </div>
            </div>
        </div>
    )
}

export default LoginPage
