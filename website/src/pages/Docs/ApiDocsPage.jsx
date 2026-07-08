import React from "react"
import SwaggerUI from "swagger-ui-react"
import "swagger-ui-react/swagger-ui.css"

const ApiDocsPage = () => {
    // Đổi thành đường dẫn tương đối để lừa trình duyệt đi qua Proxy (né lỗi CORS)
    const swaggerUrl = "/docs/api.json"

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <h2 className="text-2xl font-bold text-gray-800">Tài liệu API (Swagger)</h2>
            </div>

            <div className="glass p-2 bg-white opacity-95 floating-shadow rounded-xl overflow-hidden shadow-sm">
                {/* Swagger UI component được nhúng thẳng vào React */}
                <SwaggerUI url={swaggerUrl} />
            </div>
        </div>
    )
}

export default ApiDocsPage
