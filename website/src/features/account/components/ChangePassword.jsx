import React, { useState } from "react"
import { Lock, Loader2 } from "lucide-react"
import { Card, FormInput, Button, IconBadge } from "../../../components/common"
import useSettingsStore from "../../../store/useSettingsStore"
import apiService from "../../../api/apiService"
import toast from "react-hot-toast"

const ChangePassword = () => {
    const translate = useSettingsStore((state) => state.translate)
    const [formData, setFormData] = useState({
        current_password: "",
        password: "",
        password_confirmation: "",
    })
    const [loading, setLoading] = useState(false)

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value })
    }

    const handleSubmit = async (e) => {
        e.preventDefault()
        if (formData.password !== formData.password_confirmation) {
            toast.error(translate("account.password_mismatch") || "Password confirmation does not match!")
            return
        }

        setLoading(true)
        try {
            try {
                await apiService.post("auth/change-password", formData)
            } catch (err) {
                if (err?.response?.status === 404 || err?.response?.status === 405) {
                    await apiService.put("auth/profile", formData)
                } else {
                    throw err
                }
            }
            toast.success(translate("account.password_updated") || "Password updated successfully!")
            setFormData({ current_password: "", password: "", password_confirmation: "" })
        } catch (error) {
            const validationErrors = error?.response?.data?.errors
            const firstValidationMessage = validationErrors
                ? Object.values(validationErrors).flat()[0]
                : null
            toast.error(
                firstValidationMessage ||
                    error.response?.data?.message ||
                    translate("account.password_update_failed") ||
                    "Failed to update password."
            )
        } finally {
            setLoading(false)
        }
    }

    return (
        <Card shadow="sm" className="p-6 sm:p-8">
            <div className="flex items-center gap-3 pb-4 mb-6 border-b border-slate-100">
                <IconBadge icon={Lock} color="blue" size="sm" />
                <div>
                    <h2 className="text-lg font-bold text-[#09253E] tracking-tight">
                        {translate("account.change_password") || "Change Password"}
                    </h2>
                    <p className="text-xs text-slate-500">
                        {translate("account.change_password_subtitle") ||
                            "Ensure your account uses a strong, unique password."}
                    </p>
                </div>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4 max-w-md">
                <FormInput
                    name="current_password"
                    value={formData.current_password}
                    onChange={handleChange}
                    label={translate("account.current_password") || "Current Password"}
                    type="password"
                    icon={Lock}
                    placeholder={translate("account.current_password_placeholder") || "••••••••"}
                    required
                />
                <FormInput
                    name="password"
                    value={formData.password}
                    onChange={handleChange}
                    label={translate("account.new_password") || "New Password"}
                    type="password"
                    icon={Lock}
                    placeholder={translate("account.new_password_placeholder") || "••••••••"}
                    required
                />
                <FormInput
                    name="password_confirmation"
                    value={formData.password_confirmation}
                    onChange={handleChange}
                    label={translate("account.confirm_password") || "Confirm New Password"}
                    type="password"
                    icon={Lock}
                    placeholder={translate("account.confirm_password_placeholder") || "••••••••"}
                    required
                />

                <div className="pt-2">
                    <Button type="submit" variant="primary" size="md" disabled={loading} className="gap-2">
                        {loading && <Loader2 className="animate-spin" size={16} />}
                        <span>{translate("account.update_password") || "Update Password"}</span>
                    </Button>
                </div>
            </form>
        </Card>
    )
}

export default ChangePassword
