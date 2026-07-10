import React, { useState, useRef } from "react"
import { User, Mail, Phone, Camera, Loader2 } from "lucide-react"
import { Card, FormInput, Button, IconBadge } from "../../../components/common"
import useSettingsStore from "../../../store/useSettingsStore"
import useAuthStore from "../../auth/store/useAuthStore"
import apiService from "../../../api/apiService"
import toast from "react-hot-toast"

const ProfileDetails = ({ user }) => {
    const translate = useSettingsStore((state) => state.translate)
    const { refreshUser } = useAuthStore()
    const [isSubmitting, setIsSubmitting] = useState(false)
    const fileInputRef = useRef(null)
    const [avatarPreview, setAvatarPreview] = useState(user?.avatar || null)

    const [formData, setFormData] = useState({
        name: user?.name || "",
        email: user?.email || "",
        phone: user?.phone || "",
    })

    const updateField = (field) => (e) => setFormData({ ...formData, [field]: e.target.value })

    const handleAvatarChange = (e) => {
        const file = e.target.files?.[0]
        if (file) {
            setFormData({ ...formData, avatar: file })
            setAvatarPreview(URL.createObjectURL(file))
        }
    }

    const handleSubmit = async (e) => {
        e.preventDefault()
        setIsSubmitting(true)
        try {
            const data = new FormData()
            data.append("_method", "PUT")
            data.append("name", formData.name)
            data.append("phone", formData.phone)
            if (formData.avatar) {
                data.append("avatar", formData.avatar)
            }

            await apiService.post("auth/profile", data, {
                headers: { "Content-Type": "multipart/form-data" },
            })

            await refreshUser()
            toast.success(translate("account.profile_updated") || "Cập nhật thông tin thành công!")
        } catch (error) {
            console.error(error)
            toast.error(error.response?.data?.message || translate("account.profile_update_failed") || "Cập nhật thất bại!")
        } finally {
            setIsSubmitting(false)
        }
    }

    return (
        <div className="space-y-8">
            <Card shadow="md" className="p-8 md:p-10">
                <Card.Header className="px-0 pt-0 border-none">
                    <div className="flex items-center gap-3 mb-8">
                        <IconBadge icon={User} color="indigo" />
                        <h2 className="text-xl font-bold text-slate-900">{translate("account.profile_title")}</h2>
                    </div>
                </Card.Header>

                <Card.Body className="px-0 pb-0">
                    <form onSubmit={handleSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div className="md:col-span-2 flex flex-col items-center justify-center mb-6">
                            <div className="relative group cursor-pointer" onClick={() => fileInputRef.current?.click()}>
                                <div className="w-24 h-24 rounded-full bg-slate-100 border-4 border-white shadow-lg overflow-hidden flex items-center justify-center text-slate-400">{avatarPreview ? <img src={avatarPreview} alt="Avatar" className="w-full h-full object-cover" /> : <User size={40} />}</div>
                                <div className="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Camera size={24} className="text-white" />
                                </div>
                            </div>
                            <input type="file" ref={fileInputRef} className="hidden" accept="image/*" onChange={handleAvatarChange} />
                            <p className="text-sm text-slate-500 mt-3">{translate("account.change_avatar") || "Thay đổi ảnh đại diện"}</p>
                        </div>

                        <FormInput label={translate("account.full_name")} icon={User} value={formData.name} onChange={updateField("name")} required />
                        <FormInput label={translate("account.email")} type="email" icon={Mail} value={formData.email} disabled />
                        <FormInput label={translate("account.phone")} type="tel" icon={Phone} value={formData.phone} onChange={updateField("phone")} />

                        <div className="md:col-span-2 pt-4">
                            <Button type="submit" variant="blue" size="lg" disabled={isSubmitting} className="flex items-center justify-center gap-2">
                                {isSubmitting && <Loader2 className="animate-spin" size={18} />}
                                {translate("account.save_changes")}
                            </Button>
                        </div>
                    </form>
                </Card.Body>
            </Card>
        </div>
    )
}

export default ProfileDetails
