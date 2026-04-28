import React from "react"
import { Lock } from "lucide-react"
import useSettingsStore from "../../store/useSettingsStore"
import useResetPassword from "../../features/auth/hooks/useResetPassword"
import { FormInput, Button, Alert, BackgroundOrbs } from "../../components/common"

const ResetPasswordPage = () => {
    const translate = useSettingsStore((state) => state.translate)
    const { formData, setFormData, status, error, isLoading, handleSubmit } = useResetPassword()

    const updateField = (field) => (e) => setFormData({ ...formData, [field]: e.target.value })

    return (
        <div className="min-h-screen bg-slate-50 flex items-center justify-center relative overflow-hidden font-sans">
            <BackgroundOrbs preset="auth" />

            <div className="bg-white/60 backdrop-blur-xl p-8 md:p-12 w-full max-w-[450px] border border-white/60 shadow-[0_20px_50px_rgba(0,0,0,0.05)] rounded-[2.5rem] relative z-10 mx-4">
                <div className="text-center mb-10">
                    <h1 className="text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 mb-3 tracking-tight">{translate("auth.reset_password_title")}</h1>
                    <p className="text-slate-500 font-medium">{translate("auth.reset_password_subtitle")}</p>
                </div>

                {status && <Alert variant="success" className="mb-6">{status}</Alert>}
                {error && <Alert variant="error" className="mb-6">{error}</Alert>}

                <form onSubmit={handleSubmit} className="space-y-6">
                    <FormInput label={translate("account.new_password")} type="password" icon={Lock} placeholder="••••••••" value={formData.password} onChange={updateField("password")} required />
                    <FormInput label={translate("auth.confirm_password")} type="password" icon={Lock} placeholder="••••••••" value={formData.password_confirmation} onChange={updateField("password_confirmation")} required />

                    <Button type="submit" size="block" disabled={isLoading} className="mt-4">
                        {isLoading ? translate("auth.processing") : translate("auth.update_password_button")}
                    </Button>
                </form>
            </div>
        </div>
    )
}

export default ResetPasswordPage
