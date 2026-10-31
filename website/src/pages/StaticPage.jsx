import React from "react"
import { useLocation } from "react-router-dom"
import { usePageBySlug } from "../features/home/hooks/useHomeData"
import { PageHeading } from "../components/common"

const fallbackContent = {
    about: {
        title: "About NovaStore",
        content:
            "NovaStore is an enterprise-grade retail and distribution platform engineered for product authenticity, transparent multi-warehouse inventory, and reliable nationwide fulfillment.",
    },
    contact: {
        title: "Contact & Corporate Support",
        content:
            "Our customer operations team is available Monday through Saturday to assist with order inquiries, corporate procurement, and warranty claims.",
    },
    shipping: {
        title: "Shipping & Delivery SLA",
        content:
            "Orders are dispatched from the nearest regional warehouse within 24 hours of confirmation. Complimentary standard shipping is applied to orders above 500,000 VND.",
    },
    returns: {
        title: "Returns & Refund Policy",
        content:
            "Eligible items may be returned or exchanged within 30 days of delivery in their original packaging with full accessories and invoice documentation.",
    },
    faq: {
        title: "Frequently Asked Questions",
        content:
            "Find answers regarding real-time warehouse stock selection, payment settlement methods, and order tracking.",
    },
    privacy: {
        title: "Privacy & Data Protection",
        content:
            "Customer personal and transactional data is encrypted and processed strictly for order fulfillment and authorized account services.",
    },
    terms: {
        title: "Terms of Service",
        content:
            "These terms govern access to the NovaStore platform, product purchases, warranty coverage, and fulfillment obligations.",
    },
}

const renderBlock = (block, index) => {
    const type = block?.type || block?.block_type || "text"
    const value = block?.value ?? block?.content ?? block?.text ?? block?.body ?? ""
    const mediaUrl = block?.url || block?.media_url || block?.value?.url || block?.value?.path || ""

    switch (type) {
        case "media":
            return (
                <div
                    key={index}
                    className="rounded-xl overflow-hidden border border-slate-200/90 bg-white shadow-card"
                >
                    {mediaUrl ? (
                        <img
                            src={mediaUrl}
                            alt={block?.alt || block?.caption || "media"}
                            className="w-full h-auto"
                        />
                    ) : (
                        <div className="p-6 text-slate-500 text-sm">Media block</div>
                    )}
                    {block?.caption && <div className="p-4 text-xs text-slate-500">{block.caption}</div>}
                </div>
            )
        case "code-editor":
            return (
                <pre
                    key={index}
                    className="overflow-x-auto rounded-xl bg-[#09253E] text-slate-100 p-5 text-xs font-mono"
                >
                    <code>{String(value)}</code>
                </pre>
            )
        case "number":
            return (
                <div
                    key={index}
                    className="rounded-xl border border-slate-200/90 bg-white p-6 text-2xl font-bold text-[#09253E] shadow-card tabular-nums"
                >
                    {value}
                </div>
            )
        case "textarea":
        default:
            return (
                <div
                    key={index}
                    className="prose prose-slate max-w-none bg-white rounded-xl border border-slate-200/90 p-6 shadow-card text-sm text-slate-700 leading-relaxed"
                >
                    <p className="whitespace-pre-line">{value}</p>
                </div>
            )
    }
}

const StaticPage = () => {
    const location = useLocation()
    const pageSlug = (location.pathname.replace(/^\/+/, "") || "about").replace(/^p\//, "")
    const { data } = usePageBySlug(pageSlug)
    const page = data?.data || data || null
    const content = page || fallbackContent[pageSlug] || { title: "Information", content: "" }
    const blocks = Array.isArray(page?.blocks) ? page.blocks : []

    return (
        <div className="max-w-3xl mx-auto py-4">
            <div className="bg-white border border-slate-200/90 rounded-xl p-6 sm:p-10 shadow-card">
                <PageHeading size="lg" className="pb-4 mb-5 border-b border-slate-100">
                    {content.title}
                </PageHeading>
                {content.content && (
                    <p className="text-slate-600 text-sm sm:text-base leading-relaxed mb-6">
                        {content.content}
                    </p>
                )}

                {blocks.length > 0 ? (
                    <div className="space-y-5">{blocks.map((block, index) => renderBlock(block, index))}</div>
                ) : (
                    !content.content && (
                        <p className="text-slate-500 text-sm">Content is currently being updated.</p>
                    )
                )}
            </div>
        </div>
    )
}

export default StaticPage
