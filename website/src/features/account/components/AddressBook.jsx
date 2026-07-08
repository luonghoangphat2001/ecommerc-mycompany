import React from "react"
import { MapPin, Plus, Trash2, Edit, Check, Loader2 } from "lucide-react"
import { Card, FormInput, FormSelect, Button, PageHeading, IconBadge } from "../../../components/common"
import useSettingsStore from "../../../store/useSettingsStore"
import useUserAddress from "../../address/hooks/useUserAddress"
import useAddressBook from "../../address/hooks/useAddressBook"

const AddressBook = () => {
    const translate = useSettingsStore((state) => state.translate)
    const { user, setDefaultAddress } = useUserAddress()

    const { addresses, loading, showForm, editingAddress, formData, setFormData, countries, states, cities, wards, setShowForm, resetForm, handleEdit, handleSubmit, handleDelete, fetchAddresses } = useAddressBook()

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
            <div className="flex justify-center py-20">
                <Loader2 className="animate-spin h-10 w-10 text-blue-600" />
            </div>
        )
    }

    return (
        <div className="space-y-8">
            <div className="flex items-center justify-between">
                <PageHeading>{translate("account.addresses_title") || "Address Book"}</PageHeading>
                {!showForm && (
                    <Button variant="blue" size="md" onClick={() => setShowForm(true)} className="gap-2">
                        <Plus size={18} />
                        {translate("account.add_new_address") || "Add New"}
                    </Button>
                )}
            </div>

            {showForm && (
                <Card shadow="md" className="p-8">
                    <Card.Header className="px-0 pt-0 border-none">
                        <Card.Title className="text-2xl font-bold">{editingAddress ? translate("account.edit_address") || "Edit Address" : translate("account.add_new_address") || "New Address"}</Card.Title>
                    </Card.Header>
                    <Card.Body className="px-0 pb-0">
                        <form onSubmit={onSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <FormSelect
                                label={translate("checkout.type") || "Type"}
                                value={formData.type}
                                onChange={handleChange("type")}
                                options={[
                                    ["shipping", translate("checkout.shipping") || "Shipping"],
                                    ["billing", translate("checkout.billing") || "Billing"],
                                ]}
                            />
                            <FormInput label={translate("checkout.first_name") || "First Name"} required value={formData.first_name} onChange={handleChange("first_name")} />
                            <FormInput label={translate("checkout.last_name") || "Last Name"} required value={formData.last_name} onChange={handleChange("last_name")} />
                            <FormInput label={translate("phone") || "Phone"} type="tel" required value={formData.phone} onChange={handleChange("phone")} />
                            <FormInput label={translate("account.email") || "Email"} type="email" value={formData.email} onChange={handleChange("email")} />
                            <FormSelect label={translate("checkout.country") || "Country"} value={formData.country_code} onChange={handleChange("country_code")} options={countries.map((c) => [c.code, c.name])} />
                            <FormSelect label={translate("checkout.state") || "Province/State"} required value={formData.state_id} onChange={handleChange("state_id")} options={states.map((s) => [s.id, s.name])} placeholder={translate("checkout.select_state") || "-- Select State --"} />
                            <FormSelect label={translate("checkout.city") || "District/City"} required value={formData.city_id} onChange={handleChange("city_id")} options={cities.map((c) => [c.id, c.name])} placeholder={translate("checkout.select_city") || "-- Select City --"} />
                            <FormSelect label={translate("checkout.ward") || "Ward/Region"} required value={formData.ward_id} onChange={handleChange("ward_id")} options={wards.map((w) => [w.id, w.name])} placeholder={translate("checkout.select_ward") || "-- Select Ward --"} />
                            <FormInput label={translate("checkout.address") || "Street Address"} required value={formData.street} onChange={handleChange("street")} className="md:col-span-2" />

                            <div className="flex gap-4 pt-4 md:col-span-2">
                                <Button type="submit" variant="blue" size="lg">
                                    {translate("account.save_changes") || "Save"}
                                </Button>
                                <Button type="button" variant="secondary" size="lg" onClick={resetForm}>
                                    {translate("common.cancel") || "Cancel"}
                                </Button>
                            </div>
                        </form>
                    </Card.Body>
                </Card>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {addresses.length === 0 ? (
                    <p className="text-slate-500 text-center py-10 md:col-span-2">{translate("account.no_saved_addresses") || "No saved addresses yet."}</p>
                ) : (
                    addresses.map((addr) => (
                        <Card key={addr.id} className="p-6 border border-slate-100">
                            <div className="flex justify-between items-start">
                                <div className="flex gap-3">
                                    <IconBadge icon={MapPin} color="blue" />
                                    <div>
                                        <h3 className="font-bold text-slate-900">
                                            {addr.first_name} {addr.last_name}
                                        </h3>
                                        <span className={`inline-block text-xs px-2.5 py-0.5 rounded-full font-semibold mt-1 ${addr.type === "billing" ? "bg-purple-100 text-purple-700" : "bg-teal-100 text-teal-700"}`}>{addr.type === "billing" ? translate("checkout.billing") || "Billing" : translate("checkout.shipping") || "Shipping"}</span>
                                        <p className="text-sm text-slate-600 mt-3">{addr.street}</p>
                                        <p className="text-sm text-slate-600">
                                            {addr.ward_id}, {addr.city_id}, {addr.state_id}
                                        </p>
                                        <p className="text-sm text-slate-900 font-semibold mt-2">{addr.phone}</p>

                                        <div className="flex gap-2 mt-4 flex-wrap">
                                            <button onClick={() => handleSetDefault(addr.id, "shipping")} className={`text-xs font-bold px-3 py-1.5 rounded-xl border transition-all flex items-center gap-1 ${user?.default_shipping_address_id === addr.id ? "bg-blue-600 text-white border-blue-600" : "bg-white text-slate-600 border-slate-200 hover:bg-slate-50"}`}>
                                                {user?.default_shipping_address_id === addr.id && <Check size={12} />}
                                                {user?.default_shipping_address_id === addr.id ? translate("account.default_shipping") || "Default Shipping" : translate("account.set_shipping") || "Set Shipping"}
                                            </button>
                                            <button onClick={() => handleSetDefault(addr.id, "billing")} className={`text-xs font-bold px-3 py-1.5 rounded-xl border transition-all flex items-center gap-1 ${user?.default_billing_address_id === addr.id ? "bg-purple-600 text-white border-purple-600" : "bg-white text-slate-600 border-slate-200 hover:bg-slate-50"}`}>
                                                {user?.default_billing_address_id === addr.id && <Check size={12} />}
                                                {user?.default_billing_address_id === addr.id ? translate("account.default_billing") || "Default Billing" : translate("account.set_billing") || "Set Billing"}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div className="flex flex-col gap-2">
                                    <button onClick={() => handleEdit(addr)} className="p-2 hover:bg-slate-100 rounded-xl text-slate-500 hover:text-slate-800 transition-all">
                                        <Edit size={18} />
                                    </button>
                                    <button onClick={() => onDelete(addr.id)} className="p-2 hover:bg-red-50 rounded-xl text-slate-400 hover:text-red-500 transition-all">
                                        <Trash2 size={18} />
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
