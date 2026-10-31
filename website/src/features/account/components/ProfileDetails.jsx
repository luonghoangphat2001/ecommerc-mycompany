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
            toast.success(translate("account.profile_updated") || "Profile updated successfully!")
        } catch (error) {
            console.error(error)
            toast.error(
                error.response?.data?.message ||
                    translate("account.profile_update_failed") ||
                    "Failed to update profile."
            )
        } finally {
            setIsSubmitting(false)
        }
    }

    return (
        <Card shadow="sm" className="p-6 sm:p-8">
            <div className="flex items-center gap-3 pb-4 mb-6 border-b border-slate-100">
                <IconBadge icon={User} color="blue" size="sm" />
                <h2 className="text-lg font-bold text-[#09253E] tracking-tight">
                    {translate("account.profile_title")}
                </h2>
            </div>

            <form onSubmit={handleSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div className="md:col-span-2 flex items-center gap-5 pb-4 border-b border-slate-100">
                    <div
                        className="relative group cursor-pointer shrink-0"
                        onClick={() => fileInputRef.current?.click()}
                    >
                        <div className="w-16 h-16 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center text-slate-400">
                            {avatarPreview ? (
                                <img src={avatarPreview} alt="Avatar" className="w-full h-full object-cover" />
                            ) : (
                                <User size={28} />
                            )}
                        </div>
                        <div className="absolute inset-0 bg-slate-900/50 rounded-xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <Camera size={18} className="text-white" />
                        </div>
                    </div>
                    <div>
                        <button
                            type="button"
                            onClick={() => fileInputRef.current?.click()}
                            className="text-xs font-semibold text-[#004687] hover:underline"
                        >
                            {translate("account.change_avatar") || "Upload new avatar"}
                        </button>
                        <p className="text-xs text-slate-500 mt-0.5">JPG, PNG or WEBP. Max 2MB.</p>
                    </div>
                    <input
                        type="file"
                        ref={fileInputRef}
                        className="hidden"
                        accept="image/*"
                        onChange={handleAvatarChange}
                    />
                </div>

                <FormInput
                    label={translate("account.full_name")}
                    name="name"
                    icon={User}
                    value={formData.name}
                    onChange={updateField("name")}
                    required
                />
                <FormInput
                    label={translate("account.email")}
                    name="email"
                    type="email"
                    icon={Mail}
                    value={formData.email}
                    disabled
                />
                <FormInput
                    label={translate("account.phone")}
                    name="phone"
                    type="tel"
                    icon={Phone}
                    value={formData.phone}
                    onChange={updateField("phone")}
                />

                <div className="md:col-span-2 pt-3 border-t border-slate-100">
                    <Button type="submit" variant="primary" size="md" disabled={isSubmitting} className="gap-2">
                        {isSubmitting && <Loader2 className="animate-spin" size={16} />}
                        <span>{translate("account.save_changes")}</span>
                    </Button>
                </div>
            </form>
        </Card>
    )
}

export default ProfileDetails
