import React, { useState } from "react"
import { Lock, Loader2 } from "lucide-react"
import { Card, FormInput, Button } from "../../../components/common"
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
            toast.error(translate("account.password_mismatch") || "Mật khẩu xác nhận không khớp!")
            return
        }

        setLoading(true)
        try {
            await apiService.put("auth/profile", formData)
            toast.success(translate("account.password_updated") || "Đổi mật khẩu thành công!")
            setFormData({ current_password: "", password: "", password_confirmation: "" })
        } catch (error) {
            toast.error(error.response?.data?.message || translate("account.password_update_failed") || "Đổi mật khẩu thất bại!")
        } finally {
            setLoading(false)
        }
    }

    return (
        <Card shadow="md" className="p-8 md:p-10">
            <Card.Header className="px-0 pt-0 border-none">
                <Card.Title className="text-2xl font-black tracking-tight">{translate("account.change_password") || "Đổi mật khẩu"}</Card.Title>
                <p className="text-slate-500 mt-2">{translate("account.change_password_subtitle") || "Đảm bảo tài khoản của bạn được bảo mật bằng mật khẩu mạnh."}</p>
            </Card.Header>

            <Card.Body className="px-0 pb-0">
                <form onSubmit={handleSubmit} className="space-y-6 max-w-md">
                    <FormInput name="current_password" value={formData.current_password} onChange={handleChange} label={translate("account.current_password") || "Mật khẩu hiện tại"} type="password" icon={Lock} placeholder={translate("account.current_password_placeholder") || "Nhập mật khẩu hiện tại"} required />
                    <FormInput name="password" value={formData.password} onChange={handleChange} label={translate("account.new_password") || "Mật khẩu mới"} type="password" icon={Lock} placeholder={translate("account.new_password_placeholder") || "Nhập mật khẩu mới"} required />
                    <FormInput name="password_confirmation" value={formData.password_confirmation} onChange={handleChange} label={translate("account.confirm_password") || "Xác nhận mật khẩu mới"} type="password" icon={Lock} placeholder={translate("account.confirm_password_placeholder") || "Xác nhận mật khẩu mới"} required />

                    <Button type="submit" size="xl" disabled={loading} className="flex items-center gap-2">
                        {loading && <Loader2 className="animate-spin" size={18} />}
                        {translate("account.update_password") || "Cập nhật mật khẩu"}
                    </Button>
                </form>
            </Card.Body>
        </Card>
    )
}

export default ChangePassword
