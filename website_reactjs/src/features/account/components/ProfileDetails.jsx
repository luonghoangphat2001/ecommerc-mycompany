import React, { useState } from "react"
import { User, Mail, Phone } from "lucide-react"
import { Card, FormInput, Button, IconBadge } from "../../../components/common"
import useSettingsStore from "../../../store/useSettingsStore"

const ProfileDetails = ({ user }) => {
    const translate = useSettingsStore((state) => state.translate)
    const [formData, setFormData] = useState({
        name: user?.name || "",
        email: user?.email || "",
        phone: user?.phone || "",
    })

    const updateField = (field) => (e) => setFormData({ ...formData, [field]: e.target.value })

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
                    <form className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <FormInput label={translate("account.full_name")} icon={User} value={formData.name} onChange={updateField("name")} />
                        <FormInput label={translate("account.email")} type="email" icon={Mail} value={formData.email} disabled />
                        <FormInput label={translate("account.phone")} type="tel" icon={Phone} value={formData.phone} onChange={updateField("phone")} />

                        <div className="md:col-span-2 pt-4">
                            <Button type="submit" variant="blue" size="lg">
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
