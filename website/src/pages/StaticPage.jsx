import React from "react"
import { useLocation } from "react-router-dom"
import { usePageBySlug } from "../features/home/hooks/useHomeData"
import { PageHeading } from "../components/common"
import useSettingsStore from "../store/useSettingsStore"
import { getLocalizedText } from "../utils/formatters"
import useSeo from "../utils/useSeo"

const fallbackContent = {
    about: {
        vi: {
            title: "Về NovaStore",
            content:
                "NovaStore là nền tảng bán lẻ và phân phối chuẩn doanh nghiệp được xây dựng nhằm đảm bảo tính chính hãng của sản phẩm, quản lý tồn kho đa điểm minh bạch và giao nhận toàn quốc tin cậy.",
        },
        en: {
            title: "About NovaStore",
            content:
                "NovaStore is an enterprise-grade retail and distribution platform engineered for product authenticity, transparent multi-warehouse inventory, and reliable nationwide fulfillment.",
        },
    },
    contact: {
        vi: {
            title: "Liên hệ & Hỗ trợ Doanh nghiệp",
            content:
                "Đội ngũ vận hành khách hàng của chúng tôi làm việc từ Thứ Hai đến Thứ Bảy để hỗ trợ tra cứu đơn hàng, mua sắm doanh nghiệp và yêu cầu bảo hành.",
        },
        en: {
            title: "Contact & Corporate Support",
            content:
                "Our customer operations team is available Monday through Saturday to assist with order inquiries, corporate procurement, and warranty claims.",
        },
    },
    shipping: {
        vi: {
            title: "Chính sách Vận chuyển & Cam kết Giao nhận",
            content:
                "Đơn hàng được điều phối từ kho khu vực gần nhất trong vòng 24 giờ kể từ khi xác nhận. Miễn phí vận chuyển tiêu chuẩn cho mọi đơn hàng từ 500.000₫.",
        },
        en: {
            title: "Shipping & Delivery SLA",
            content:
                "Orders are dispatched from the nearest regional warehouse within 24 hours of confirmation. Complimentary standard shipping is applied to orders above 500,000 VND.",
        },
    },
    returns: {
        vi: {
            title: "Chính sách Đổi trả & Hoàn tiền",
            content:
                "Sản phẩm đủ điều kiện có thể được đổi hoặc trả trong vòng 30 ngày kể từ ngày nhận hàng trong tình trạng nguyên hộp, đầy đủ phụ kiện và hóa đơn.",
        },
        en: {
            title: "Returns & Refund Policy",
            content:
                "Eligible items may be returned or exchanged within 30 days of delivery in their original packaging with full accessories and invoice documentation.",
        },
    },
    faq: {
        vi: {
            title: "Câu hỏi thường gặp",
            content:
                "Tìm câu trả lời chi tiết về cách chọn kho còn hàng theo thời gian thực, các phương thức thanh toán và tra cứu hành trình đơn hàng.",
        },
        en: {
            title: "Frequently Asked Questions",
            content:
                "Find answers regarding real-time warehouse stock selection, payment settlement methods, and order tracking.",
        },
    },
    privacy: {
        vi: {
            title: "Chính sách Bảo mật & Bảo vệ Dữ liệu",
            content:
                "Dữ liệu cá nhân và giao dịch của khách hàng được mã hóa và chỉ xử lý phục vụ mục đích hoàn tất đơn hàng cũng như các dịch vụ tài khoản được ủy quyền.",
        },
        en: {
            title: "Privacy & Data Protection",
            content:
                "Customer personal and transactional data is encrypted and processed strictly for order fulfillment and authorized account services.",
        },
    },
    terms: {
        vi: {
            title: "Điều khoản Dịch vụ",
            content:
                "Các điều khoản này điều chỉnh việc truy cập nền tảng NovaStore, giao dịch mua sắm sản phẩm, phạm vi bảo hành và nghĩa vụ giao nhận.",
        },
        en: {
            title: "Terms of Service",
            content:
                "These terms govern access to the NovaStore platform, product purchases, warranty coverage, and fulfillment obligations.",
        },
    },
}

const renderBlock = (block, index, language) => {
    const type = block?.type || block?.block_type || "text"
    const rawValue = block?.value ?? block?.content ?? block?.text ?? block?.body ?? ""
    const value = getLocalizedText(rawValue, language)
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
                            alt={getLocalizedText(block?.alt || block?.caption, language) || "media"}
                            className="w-full h-auto"
                        />
                    ) : (
                        <div className="p-6 text-slate-500 text-sm">Media block</div>
                    )}
                    {block?.caption && (
                        <div className="p-4 text-xs text-slate-500">
                            {getLocalizedText(block.caption, language)}
                        </div>
                    )}
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
        case "heading": {
            const level = Number(block?.level) || 2
            const HeadingTag = `h${Math.min(Math.max(level, 1), 6)}`
            const headingClasses = {
                1: "text-2xl sm:text-3xl font-extrabold text-slate-900 mt-8 mb-4",
                2: "text-xl sm:text-2xl font-bold text-slate-900 mt-6 mb-3",
                3: "text-lg sm:text-xl font-bold text-slate-800 mt-5 mb-2",
                4: "text-base sm:text-lg font-semibold text-slate-800 mt-4 mb-2",
                5: "text-sm sm:text-base font-semibold text-slate-700 mt-3 mb-1",
                6: "text-xs sm:text-sm font-semibold text-slate-600 mt-2 mb-1",
            }
            return (
                <HeadingTag key={index} className={headingClasses[level] || headingClasses[2]}>
                    {value}
                </HeadingTag>
            )
        }
        case "gallery": {
            const galleryList = Array.isArray(block?.images)
                ? block.images
                : (Array.isArray(block?.value) ? block.value : [])
            return (
                <div key={index} className="grid grid-cols-2 sm:grid-cols-3 gap-4 my-4">
                    {galleryList.filter(Boolean).map((imgUrl, gIdx) => (
                        <div key={gIdx} className="rounded-xl overflow-hidden border border-slate-200/90 shadow-card bg-white aspect-video sm:aspect-square">
                            <img src={imgUrl} alt={`Gallery ${gIdx + 1}`} className="w-full h-full object-cover hover:scale-105 transition-transform duration-300" />
                        </div>
                    ))}
                </div>
            )
        }
        case "editor":
        case "markdown":
        case "html":
            return (
                <div
                    key={index}
                    className="prose prose-slate max-w-none bg-white rounded-xl border border-slate-200/90 p-6 sm:p-8 shadow-card text-sm sm:text-base text-slate-700 leading-relaxed"
                    dangerouslySetInnerHTML={{ __html: value }}
                />
            )
        case "textarea":
        default:
            if (typeof value === "string" && /<[a-z][\s\S]*>/i.test(value)) {
                return (
                    <div
                        key={index}
                        className="prose prose-slate max-w-none bg-white rounded-xl border border-slate-200/90 p-6 sm:p-8 shadow-card text-sm sm:text-base text-slate-700 leading-relaxed"
                        dangerouslySetInnerHTML={{ __html: value }}
                    />
                )
            }
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
    const language = useSettingsStore((state) => state.language) || "vi"
    const translate = useSettingsStore((state) => state.translate)
    const pageSlug = (location.pathname.replace(/^\/+/, "") || "about").replace(/^p\//, "")
    const { data } = usePageBySlug(pageSlug)
    const page = data?.data || data || null

    const fallbackEntry =
        fallbackContent[pageSlug]?.[language] ||
        fallbackContent[pageSlug]?.vi || {
            title: translate(`static.${pageSlug}.title`) || pageSlug,
            content: translate(`static.${pageSlug}.content`) || "",
        }

    const pageTitle = getLocalizedText(page?.title, language) || fallbackEntry.title
    const pageContent = getLocalizedText(page?.content, language) || fallbackEntry.content
    const blocks = Array.isArray(page?.blocks) ? page.blocks : []

    useSeo({
        title: pageTitle,
        description: pageContent,
        canonicalPath: location.pathname,
    })

    return (
        <div className="max-w-3xl mx-auto py-4">
            <div className="bg-white border border-slate-200/90 rounded-xl p-6 sm:p-10 shadow-card">
                <PageHeading size="lg" className="pb-4 mb-5 border-b border-slate-100">
                    {pageTitle}
                </PageHeading>
                {pageContent && (
                    <div
                        className="prose prose-slate max-w-none text-slate-600 text-sm sm:text-base leading-relaxed mb-6"
                        dangerouslySetInnerHTML={{ __html: pageContent }}
                    />
                )}

                {blocks.length > 0 ? (
                    <div className="space-y-5">
                        {blocks.map((block, index) => renderBlock(block, index, language))}
                    </div>
                ) : (
                    !pageContent && (
                        <p className="text-slate-500 text-sm">
                            {translate("static.updating") || "Content is currently being updated."}
                        </p>
                    )
                )}
            </div>
        </div>
    )
}

export default StaticPage
