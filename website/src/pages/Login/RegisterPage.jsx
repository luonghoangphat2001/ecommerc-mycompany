import React from "react"
import { User, Mail, Lock, Phone, ArrowLeft } from "lucide-react"
import { Link } from "react-router-dom"
import useSettingsStore from "../../store/useSettingsStore"
import useRegisterForm from "../../features/auth/hooks/useRegisterForm"
import { FormInput, Button, Alert, Logo } from "../../components/common"
import useSeo from "../../utils/useSeo"

const RegisterPage = () => {
    const translate = useSettingsStore((state) => state.translate)
    const { formData, setFormData, error, handleRegister, isLoading } = useRegisterForm()

    useSeo({
        title: translate("auth.register_title") || translate("auth.register_button") || "Create Account",
        description: translate("auth.register_subtitle"),
        canonicalPath: "/register",
    })

    const updateField = (field) => (e) => setFormData({ ...formData, [field]: e.target.value })

    return (
        <div className="min-h-screen bg-[#F8FAFC] flex flex-col items-center justify-center p-4 py-10 font-sans">
            <div className="w-full max-w-[460px] mb-6 flex items-center justify-between">
                <Logo />
                <Link
                    to="/"
                    className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#004687] transition-colors"
                >
                    <ArrowLeft size={14} />
                    <span>{translate("common.back_to_storefront") || "Storefront"}</span>
                </Link>
            </div>

            <div className="bg-white p-7 sm:p-9 w-full max-w-[460px] border border-slate-200/90 shadow-card rounded-xl">
                <div className="mb-6 pb-4 border-b border-slate-100">
                    <h1 className="text-xl font-bold text-[#09253E] tracking-tight">
                        {translate("auth.register_button") || "Create Account"}
                    </h1>
                    <p className="text-slate-500 text-xs mt-1">{translate("auth.register_subtitle")}</p>
                </div>

                {error && (
                    <Alert variant="error" className="mb-5">
                        {error}
                    </Alert>
                )}

                <form onSubmit={handleRegister} className="space-y-4">
                    <FormInput
                        label={translate("auth.full_name")}
                        name="name"
                        icon={User}
                        placeholder={translate("auth.full_name") || "Full Name"}
                        value={formData.name}
                        onChange={updateField("name")}
                        required
                    />
                    <FormInput
                        label={translate("auth.email")}
                        name="email"
                        type="email"
                        icon={Mail}
                        placeholder="email@example.com"
                        value={formData.email}
                        onChange={updateField("email")}
                        required
                    />
                    <FormInput
                        label={translate("auth.phone")}
                        name="phone"
                        type="tel"
                        icon={Phone}
                        placeholder="0987 654 321"
                        value={formData.phone}
                        onChange={updateField("phone")}
                        required
                    />

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <FormInput
                            label={translate("auth.password")}
                            name="password"
                            type="password"
                            icon={Lock}
                            placeholder="••••••••"
                            value={formData.password}
                            onChange={updateField("password")}
                            required
                        />
                        <FormInput
                            label={translate("auth.confirm_password")}
                            name="password_confirmation"
                            type="password"
                            icon={Lock}
                            placeholder="••••••••"
                            value={formData.password_confirmation}
                            onChange={updateField("password_confirmation")}
                            required
                        />
                    </div>

                    <Button type="submit" variant="primary" size="block" disabled={isLoading} className="mt-2 h-11">
                        {isLoading ? translate("auth.processing") : translate("auth.register_button")}
                    </Button>
                </form>

                <div className="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500">
                    <span>{translate("auth.have_account")} </span>
                    <Link to="/login" className="font-bold text-[#004687] hover:underline ml-1">
                        {translate("auth.login_button")}
                    </Link>
                </div>
            </div>
        </div>
    )
}

export default RegisterPage
