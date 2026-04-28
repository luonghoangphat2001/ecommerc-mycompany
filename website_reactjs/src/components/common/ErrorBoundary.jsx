import React from "react"
import Error from "./Error"

class ErrorBoundary extends React.Component {
    constructor(props) {
        super(props)
        this.state = { hasError: false, error: null }
    }

    static getDerivedStateFromError(error) {
        return { hasError: true, error }
    }

    componentDidCatch(error, errorInfo) {
        console.error("ErrorBoundary caught an error", error, errorInfo)
    }

    render() {
        if (this.state.hasError) {
            return <Error message="Hệ thống đã xảy ra sự cố. Vui lòng tải lại trang." />
        }

        return this.props.children
    }
}

export default ErrorBoundary
