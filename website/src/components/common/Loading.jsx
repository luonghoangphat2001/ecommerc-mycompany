import React from "react"

const Loading = ({ message }) => (
    <div className="w-full py-24 flex flex-col items-center justify-center gap-3">
        <div className="w-8 h-8 border-2 border-slate-200 border-t-[#004687] rounded-full animate-spin"></div>
        {message && <p className="text-sm text-slate-500 font-medium">{message}</p>}
    </div>
)

export default Loading
