import React from "react"

const Error = ({ message }) => (
    <div className="w-full h-96 flex flex-col items-center justify-center">
        <p className="text-red-500 font-medium bg-red-50 px-6 py-4 rounded-xl border border-red-100">{message || "An error occurred"}</p>
    </div>
)

export default Error
