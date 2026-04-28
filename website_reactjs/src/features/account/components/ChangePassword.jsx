import React from "react"
import { Lock } from "lucide-react"
import { Card, FormInput, Button } from "../../../components/common"
import useSettingsStore from "../../../store/useSettingsStore"

const ChangePassword = () => {
    const translate = useSettingsStore((state) => state.translate)

    return (
        <Card shadow="md" className="p-8 md:p-10">
            <Card.Header className="px-0 pt-0 border-none">
                <Card.Title className="text-2xl font-black tracking-tight">
                    {translate("account.change_password") || "Đổi mật khẩu"}
                </Card.Title>
                <p className="text-slate-500 mt-2">{translate("account.change_password_subtitle") || "Đảm bảo tài khoản của bạn được bảo mật bằng mật khẩu mạnh."}</p>
            </Card.Header>

            <Card.Body className="px-0 pb-0">
                <form className="space-y-6 max-w-md">
                    <FormInput label={translate("account.current_password") || "Mật khẩu hiện tại"} type="password" icon={Lock} placeholder={translate("account.current_password_placeholder") || "Nhập mật khẩu hiện tại"} />
                    <FormInput label={translate("account.new_password") || "Mật khẩu mới"} type="password" icon={Lock} placeholder={translate("account.new_password_placeholder") || "Nhập mật khẩu mới"} />
                    <FormInput label={translate("account.confirm_password") || "Xác nhận mật khẩu mới"} type="password" icon={Lock} placeholder={translate("account.confirm_password_placeholder") || "Xác nhận mật khẩu mới"} />

                    <Button type="submit" size="xl">
                        {translate("account.update_password") || "Cập nhật mật khẩu"}
                    </Button>
                </form>
            </Card.Body>
        </Card>
    )
}

export default ChangePassword
