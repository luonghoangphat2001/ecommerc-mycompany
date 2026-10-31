import React from "react"
import { Lock } from "lucide-react"
import useSettingsStore from "../../store/useSettingsStore"
import useResetPassword from "../../features/auth/hooks/useResetPassword"
import { FormInput, Button, Alert, Logo } from "../../components/common"

const ResetPasswordPage = () => {
    const translate = useSettingsStore((state) => state.translate)
    const { formData, setFormData, status, error, isLoading, handleSubmit } = useResetPassword()

    const updateField = (field) => (e) => setFormData({ ...formData, [field]: e.target.value })

    return (
        <div className="min-h-screen bg-[#F8FAFC] flex flex-col items-center justify-center p-4 font-sans">
            <div className="mb-6">
                <Logo />
            </div>

            <div className="bg-white p-7 sm:p-9 w-full max-w-[420px] border border-slate-200/90 shadow-card rounded-xl">
                <div className="mb-6 pb-4 border-b border-slate-100">
                    <h1 className="text-xl font-bold text-[#09253E] tracking-tight">
                        {translate("auth.reset_password_title")}
                    </h1>
                    <p className="text-slate-500 text-xs mt-1">{translate("auth.reset_password_subtitle")}</p>
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
                        label={translate("account.new_password")}
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

                    <Button type="submit" variant="primary" size="block" disabled={isLoading} className="mt-2 h-11">
                        {isLoading ? translate("auth.processing") : translate("auth.update_password_button")}
                    </Button>
                </form>
            </div>
        </div>
    )
}

export default ResetPasswordPage
