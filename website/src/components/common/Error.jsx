import React from "react"
import { AlertCircle } from "lucide-react"

const Error = ({ message }) => (
    <div className="w-full py-20 flex flex-col items-center justify-center px-4">
        <div className="flex items-center gap-3 text-red-700 text-sm font-medium bg-red-50 px-5 py-3.5 rounded-lg border border-red-200 max-w-md">
            <AlertCircle size={18} className="flex-shrink-0 text-red-600" />
            <span>{message || "An unexpected error occurred."}</span>
        </div>
    </div>
)

export default Error
