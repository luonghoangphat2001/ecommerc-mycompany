import React from "react"
import { MapPin, Plus, Trash2, Edit, Check, Loader2 } from "lucide-react"
import { Card, FormInput, FormSelect, Button, IconBadge } from "../../../components/common"
import useSettingsStore from "../../../store/useSettingsStore"
import useUserAddress from "../../address/hooks/useUserAddress"
import useAddressBook from "../../address/hooks/useAddressBook"

const AddressBook = () => {
    const translate = useSettingsStore((state) => state.translate)
    const { user, setDefaultAddress } = useUserAddress()

    const {
        addresses,
        loading,
        showForm,
        editingAddress,
        formData,
        setFormData,
        countries,
        states,
        cities,
        wards,
        setShowForm,
        resetForm,
        handleEdit,
        handleSubmit,
        handleDelete,
        fetchAddresses,
    } = useAddressBook()

    const handleChange = (field) => (e) => {
        const updates = { [field]: e.target.value }
        if (field === "country_code") {
            updates.state_id = ""
            updates.city_id = ""
            updates.ward_id = ""
        } else if (field === "state_id") {
            updates.city_id = ""
            updates.ward_id = ""
        } else if (field === "city_id") {
            updates.ward_id = ""
        }
        setFormData((prev) => ({ ...prev, ...updates }))
    }

    const handleSetDefault = async (id, type) => {
        const success = await setDefaultAddress(id, type)
        if (success) alert(translate("account.default_address_updated") || "Default address updated")
    }

    const onDelete = async (id) => {
        if (window.confirm(translate("account.confirm_delete") || "Are you sure?")) {
            const success = await handleDelete(id)
            if (success) fetchAddresses()
        }
    }

    const onSubmit = async (e) => {
        e.preventDefault()
        const success = await handleSubmit(e)
        if (success) fetchAddresses()
    }

    if (loading) {
        return (
            <div className="flex justify-center py-16">
                <Loader2 className="animate-spin h-8 w-8 text-[#004687]" />
            </div>
        )
    }

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between bg-white border border-slate-200/90 rounded-xl px-6 py-4 shadow-card">
                <h2 className="text-lg font-bold text-[#09253E] tracking-tight">
                    {translate("account.addresses_title") || "Address Book"}
                </h2>
                {!showForm && (
                    <Button variant="primary" size="sm" onClick={() => setShowForm(true)} className="gap-1.5">
                        <Plus size={15} />
                        <span>{translate("account.add_new_address") || "Add Address"}</span>
                    </Button>
                )}
            </div>

            {showForm && (
                <Card shadow="sm" className="p-6 sm:p-8">
                    <div className="pb-4 mb-5 border-b border-slate-100">
                        <h3 className="text-base font-bold text-[#09253E]">
                            {editingAddress
                                ? translate("account.edit_address") || "Edit Address"
                                : translate("account.add_new_address") || "New Address"}
                        </h3>
                    </div>
                    <form onSubmit={onSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <FormSelect
                            label={translate("checkout.type") || "Address Type"}
                            value={formData.type}
                            onChange={handleChange("type")}
                            options={[
                                ["shipping", translate("checkout.shipping") || "Shipping"],
                                ["billing", translate("checkout.billing") || "Billing"],
                            ]}
                        />
                        <FormInput
                            label={translate("checkout.first_name") || "First Name"}
                            required
                            value={formData.first_name}
                            onChange={handleChange("first_name")}
                        />
                        <FormInput
                            label={translate("checkout.last_name") || "Last Name"}
                            required
                            value={formData.last_name}
                            onChange={handleChange("last_name")}
                        />
                        <FormInput
                            label={translate("phone") || "Phone"}
                            type="tel"
                            required
                            value={formData.phone}
                            onChange={handleChange("phone")}
                        />
                        <FormInput
                            label={translate("account.email") || "Email"}
                            type="email"
                            value={formData.email}
                            onChange={handleChange("email")}
                        />
                        <FormSelect
                            label={translate("checkout.country") || "Country"}
                            value={formData.country_code}
                            onChange={handleChange("country_code")}
                            options={countries.map((c) => [c.code, c.name])}
                        />
                        <FormSelect
                            label={translate("checkout.state") || "Province / State"}
                            required
                            value={formData.state_id}
                            onChange={handleChange("state_id")}
                            options={states.map((s) => [s.id, s.name])}
                            placeholder={translate("checkout.select_state") || "-- Select State --"}
                        />
                        <FormSelect
                            label={translate("checkout.city") || "District / City"}
                            required
                            value={formData.city_id}
                            onChange={handleChange("city_id")}
                            options={cities.map((c) => [c.id, c.name])}
                            placeholder={translate("checkout.select_city") || "-- Select City --"}
                        />
                        <FormSelect
                            label={translate("checkout.ward") || "Ward / Region"}
                            required
                            value={formData.ward_id}
                            onChange={handleChange("ward_id")}
                            options={wards.map((w) => [w.id, w.name])}
                            placeholder={translate("checkout.select_ward") || "-- Select Ward --"}
                        />
                        <FormInput
                            label={translate("checkout.address") || "Street Address"}
                            required
                            value={formData.street}
                            onChange={handleChange("street")}
                            className="md:col-span-2"
                        />

                        <div className="flex gap-3 pt-3 md:col-span-2">
                            <Button type="submit" variant="primary" size="md">
                                {translate("account.save_changes") || "Save Address"}
                            </Button>
                            <Button type="button" variant="outline" size="md" onClick={resetForm}>
                                {translate("common.cancel") || "Cancel"}
                            </Button>
                        </div>
                    </form>
                </Card>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                {addresses.length === 0 ? (
                    <div className="bg-white border border-slate-200/90 rounded-xl p-10 text-center md:col-span-2 shadow-card">
                        <p className="text-sm text-slate-500">
                            {translate("account.no_saved_addresses") || "No saved addresses yet."}
                        </p>
                    </div>
                ) : (
                    addresses.map((addr) => (
                        <Card key={addr.id} className="p-5">
                            <div className="flex justify-between items-start gap-3">
                                <div className="flex gap-3 min-w-0">
                                    <IconBadge icon={MapPin} color="blue" size="sm" />
                                    <div className="min-w-0 tabular-nums">
                                        <div className="flex items-center gap-2 flex-wrap">
                                            <h3 className="font-bold text-sm text-[#09253E]">
                                                {addr.first_name} {addr.last_name}
                                            </h3>
                                            <span
                                                className={`inline-block text-[11px] px-2 py-0.5 rounded font-semibold ${
                                                    addr.type === "billing"
                                                        ? "bg-slate-100 text-slate-700 border border-slate-200"
                                                        : "bg-sky-50 text-[#004687] border border-sky-200/70"
                                                }`}
                                            >
                                                {addr.type === "billing"
                                                    ? translate("checkout.billing") || "Billing"
                                                    : translate("checkout.shipping") || "Shipping"}
                                            </span>
                                        </div>
                                        <p className="text-xs text-slate-600 mt-2">{addr.street}</p>
                                        <p className="text-xs text-slate-500">
                                            {[addr.ward_id, addr.city_id, addr.state_id].filter(Boolean).join(", ")}
                                        </p>
                                        <p className="text-xs text-slate-900 font-semibold mt-1.5">{addr.phone}</p>

                                        <div className="flex gap-2 mt-3.5 flex-wrap">
                                            <button
                                                type="button"
                                                onClick={() => handleSetDefault(addr.id, "shipping")}
                                                className={`text-[11px] font-semibold px-2.5 py-1 rounded-md border transition-colors inline-flex items-center gap-1 ${
                                                    user?.default_shipping_address_id === addr.id
                                                        ? "bg-[#004687] text-white border-[#004687]"
                                                        : "bg-white text-slate-600 border-slate-200 hover:bg-slate-50"
                                                }`}
                                            >
                                                {user?.default_shipping_address_id === addr.id && <Check size={11} />}
                                                <span>
                                                    {user?.default_shipping_address_id === addr.id
                                                        ? translate("account.default_shipping") || "Default Shipping"
                                                        : translate("account.set_shipping") || "Set Shipping"}
                                                </span>
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => handleSetDefault(addr.id, "billing")}
                                                className={`text-[11px] font-semibold px-2.5 py-1 rounded-md border transition-colors inline-flex items-center gap-1 ${
                                                    user?.default_billing_address_id === addr.id
                                                        ? "bg-[#09253E] text-white border-[#09253E]"
                                                        : "bg-white text-slate-600 border-slate-200 hover:bg-slate-50"
                                                }`}
                                            >
                                                {user?.default_billing_address_id === addr.id && <Check size={11} />}
                                                <span>
                                                    {user?.default_billing_address_id === addr.id
                                                        ? translate("account.default_billing") || "Default Billing"
                                                        : translate("account.set_billing") || "Set Billing"}
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div className="flex items-center gap-1 shrink-0">
                                    <button
                                        type="button"
                                        onClick={() => handleEdit(addr)}
                                        className="p-1.5 hover:bg-slate-100 rounded-lg text-slate-500 hover:text-slate-800 transition-colors"
                                    >
                                        <Edit size={15} />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => onDelete(addr.id)}
                                        className="p-1.5 hover:bg-red-50 rounded-lg text-slate-400 hover:text-red-600 transition-colors"
                                    >
                                        <Trash2 size={15} />
                                    </button>
                                </div>
                            </div>
                        </Card>
                    ))
                )}
            </div>
        </div>
    )
}

export default AddressBook
