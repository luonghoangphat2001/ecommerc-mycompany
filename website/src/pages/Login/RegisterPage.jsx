import React from "react"
import { User, Mail, Lock, Phone } from "lucide-react"
import { Link } from "react-router-dom"
import useSettingsStore from "../../store/useSettingsStore"
import useRegisterForm from "../../features/auth/hooks/useRegisterForm"
import { FormInput, Button, Alert, BackgroundOrbs } from "../../components/common"

const RegisterPage = () => {
    const translate = useSettingsStore((state) => state.translate)
    const { formData, setFormData, error, handleRegister, isLoading } = useRegisterForm()

    const updateField = (field) => (e) => setFormData({ ...formData, [field]: e.target.value })

    return (
        <div className="min-h-screen bg-slate-50 flex items-center justify-center relative overflow-hidden font-sans py-12">
            <BackgroundOrbs preset="auth" />

            <div className="bg-white/60 backdrop-blur-xl p-8 md:p-12 w-full max-w-[500px] border border-white/60 shadow-[0_20px_50px_rgba(0,0,0,0.05)] rounded-[2.5rem] relative z-10 mx-4">
                <div className="text-center mb-10">
                    <h1 className="text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 mb-3 tracking-tight">NovaStore</h1>
                    <p className="text-slate-500 font-medium">{translate("auth.register_subtitle")}</p>
                </div>

                {error && (
                    <Alert variant="error" className="mb-6 flex items-center">
                        {error}
                    </Alert>
                )}

                <form onSubmit={handleRegister} className="space-y-5">
                    <FormInput label={translate("auth.full_name")} icon={User} placeholder="Nguyễn Văn A" value={formData.name} onChange={updateField("name")} required />
                    <FormInput label={translate("auth.email")} type="email" icon={Mail} placeholder="email@example.com" value={formData.email} onChange={updateField("email")} required />
                    <FormInput label={translate("auth.phone")} type="tel" icon={Phone} placeholder="0987 654 321" value={formData.phone} onChange={updateField("phone")} required />

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormInput label={translate("auth.password")} type="password" icon={Lock} placeholder="••••••••" value={formData.password} onChange={updateField("password")} required />
                        <FormInput label={translate("auth.confirm_password")} type="password" icon={Lock} placeholder="••••••••" value={formData.password_confirmation} onChange={updateField("password_confirmation")} required />
                    </div>

                    <Button type="submit" size="block" disabled={isLoading} className="mt-4">
                        {isLoading ? translate("auth.processing") : translate("auth.register_button")}
                    </Button>
                </form>

                <div className="mt-10 text-center text-sm">
                    <span className="text-slate-500">{translate("auth.have_account")} </span>
                    <Link to="/login" className="font-bold text-blue-600 hover:text-blue-700 transition-colors ml-1">
                        {translate("auth.login_button")}
                    </Link>
                </div>
            </div>
        </div>
    )
}

export default RegisterPage
