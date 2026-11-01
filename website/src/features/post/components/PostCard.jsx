import React from "react"
import { Link } from "react-router-dom"
import { Calendar, User, Clock, Tag, ArrowRight, Eye, FileText } from "lucide-react"
import { formatDate } from "../../../utils/date"
import { truncateText, getLocalizedText } from "../../../utils/formatters"
import useSettingsStore from "../../../store/useSettingsStore"

const PostCard = ({ post, variant = "default" }) => {
    const translate = useSettingsStore((state) => state.translate)

    if (!post) return null

    const {
        slug,
        author,
        tags = [],
        published_at,
        created_at,
        view_count = 0,
        read_time,
        is_featured = false,
    } = post

    const title = getLocalizedText(post.title)
    const excerpt = getLocalizedText(post.excerpt)
    const content = getLocalizedText(post.content)

    const postImage = post.image_url || post.image?.url || post.featured_image || null
    const postCategory = post.category || post.categories?.[0]
    const categoryName = getLocalizedText(postCategory?.name)
    const estimatedReadTime = read_time || Math.ceil((content?.split(/\s+/)?.length || 0) / 200)

    const isHorizontal = variant === "horizontal"
    const isCompact = variant === "compact"

    if (isCompact) {
        return (
            <Link
                to={`/posts/${slug}`}
                className="flex gap-3.5 p-3.5 bg-white border border-slate-200/90 rounded-xl hover:border-[#004687]/60 transition-colors group shadow-card"
            >
                {postImage ? (
                    <img
                        src={postImage}
                        alt={title}
                        className="w-16 h-16 object-cover rounded-lg flex-shrink-0 border border-slate-200/70"
                        onError={(e) => {
                            e.target.style.display = "none"
                        }}
                    />
                ) : (
                    <div className="w-16 h-16 bg-slate-100 text-slate-400 rounded-lg border border-slate-200/70 flex items-center justify-center flex-shrink-0">
                        <FileText size={20} />
                    </div>
                )}
                <div className="flex-1 min-w-0">
                    {categoryName && (
                        <span className="text-[11px] text-[#004687] font-semibold uppercase tracking-wider">
                            {categoryName}
                        </span>
                    )}
                    <h4 className="text-sm font-bold text-[#09253E] line-clamp-2 group-hover:text-[#004687] transition-colors">
                        {title}
                    </h4>
                    <div className="flex items-center gap-3 mt-1.5 text-xs text-slate-500 tabular-nums">
                        <span className="flex items-center gap-1">
                            <Calendar size={11} />
                            {formatDate(published_at || created_at)}
                        </span>
                    </div>
                </div>
            </Link>
        )
    }

    if (isHorizontal) {
        return (
            <div className="flex flex-col md:flex-row bg-white rounded-xl border border-slate-200/90 overflow-hidden shadow-card hover:shadow-card-hover transition-all duration-200 group">
                <div className="md:w-2/5 relative overflow-hidden bg-slate-100 border-b md:border-b-0 md:border-r border-slate-100">
                    {postImage ? (
                        <img
                            src={postImage}
                            alt={title}
                            className="w-full h-56 md:h-full object-cover group-hover:scale-[1.03] transition-transform duration-300"
                            onError={(e) => {
                                e.target.style.display = "none"
                                e.target.nextSibling.style.display = "flex"
                            }}
                        />
                    ) : null}
                    <div
                        className={`w-full h-56 md:h-full bg-slate-100 text-slate-400 flex items-center justify-center ${
                            postImage ? "hidden" : ""
                        }`}
                    >
                        <FileText size={36} />
                    </div>
                    {is_featured && (
                        <span className="absolute top-3 left-3 px-2.5 py-0.5 bg-[#09253E] text-white text-[11px] font-semibold rounded-md">
                            {translate?.("product.featured") || "Featured"}
                        </span>
                    )}
                </div>

                <div className="md:w-3/5 p-6 sm:p-7 flex flex-col">
                    {categoryName && (
                        <Link
                            to={`/posts?category=${postCategory.slug}`}
                            className="text-xs text-[#004687] font-semibold uppercase tracking-wider mb-2"
                        >
                            {categoryName}
                        </Link>
                    )}

                    <h3 className="text-xl font-bold text-[#09253E] mb-2.5 group-hover:text-[#004687] transition-colors tracking-tight">
                        <Link to={`/posts/${slug}`}>{title}</Link>
                    </h3>

                    <p className="text-sm text-slate-600 mb-4 line-clamp-3 leading-relaxed">
                        {truncateText(excerpt || content, 200)}
                    </p>

                    {tags.length > 0 && (
                        <div className="flex flex-wrap gap-1.5 mb-4">
                            {tags.slice(0, 3).map((tag) => (
                                <span
                                    key={tag.id || tag}
                                    className="px-2 py-0.5 bg-slate-100 text-slate-600 text-xs rounded-md"
                                >
                                    <Tag size={10} className="inline mr-1" />
                                    {getLocalizedText(tag.name || tag)}
                                </span>
                            ))}
                        </div>
                    )}

                    <div className="mt-auto flex items-center justify-between pt-4 border-t border-slate-100 tabular-nums">
                        <div className="flex items-center gap-4 text-xs text-slate-500">
                            {author && (
                                <span className="flex items-center gap-1.5">
                                    <User size={13} />
                                    {author.name || author}
                                </span>
                            )}
                            <span className="flex items-center gap-1.5">
                                <Calendar size={13} />
                                {formatDate(published_at || created_at)}
                            </span>
                            {estimatedReadTime > 0 && (
                                <span className="flex items-center gap-1.5">
                                    <Clock size={13} />
                                    {estimatedReadTime} {translate?.("blog.min_read") || "min"}
                                </span>
                            )}
                        </div>

                        <Link
                            to={`/posts/${slug}`}
                            className="inline-flex items-center gap-1 text-xs font-bold text-[#004687] hover:text-[#09253E] transition-colors"
                        >
                            <span>{translate?.("blog.read_article") || "Read Article"}</span>
                            <ArrowRight size={14} />
                        </Link>
                    </div>
                </div>
            </div>
        )
    }

    return (
        <article className="bg-white rounded-xl border border-slate-200/90 overflow-hidden shadow-card hover:shadow-card-hover hover:border-[#004687]/60 transition-all duration-200 group flex flex-col">
            <div className="relative aspect-[16/10] overflow-hidden bg-slate-100 border-b border-slate-100">
                {postImage ? (
                    <img
                        src={postImage}
                        alt={title}
                        className="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300"
                        onError={(e) => {
                            e.target.style.display = "none"
                            e.target.nextSibling.style.display = "flex"
                        }}
                    />
                ) : null}
                <div
                    className={`w-full h-full bg-slate-100 text-slate-400 flex items-center justify-center ${
                        postImage ? "hidden" : ""
                    }`}
                >
                    <FileText size={32} />
                </div>

                {categoryName && (
                    <Link
                        to={`/posts?category=${postCategory.slug}`}
                        className="absolute top-3 left-3 px-2.5 py-0.5 bg-white/95 text-[#004687] text-[11px] font-semibold rounded-md shadow-card"
                    >
                        {categoryName}
                    </Link>
                )}

                {is_featured && (
                    <span className="absolute top-3 right-3 px-2.5 py-0.5 bg-[#09253E] text-white text-[11px] font-semibold rounded-md">
                        {translate?.("product.featured") || "Featured"}
                    </span>
                )}

                {view_count > 0 && (
                    <div className="absolute bottom-2.5 left-2.5 flex items-center gap-1 px-2 py-0.5 bg-slate-900/75 text-white text-[11px] rounded tabular-nums">
                        <Eye size={11} />
                        {view_count.toLocaleString()}
                    </div>
                )}
            </div>

            <div className="p-5 flex flex-col flex-1">
                <h3 className="text-base font-bold text-[#09253E] mb-2 line-clamp-2 group-hover:text-[#004687] transition-colors leading-snug">
                    <Link to={`/posts/${slug}`}>{title}</Link>
                </h3>

                <p className="text-slate-600 text-xs mb-4 line-clamp-2 leading-relaxed">
                    {truncateText(excerpt || content, 120)}
                </p>

                <div className="mt-auto flex items-center justify-between pt-3.5 border-t border-slate-100 tabular-nums text-xs text-slate-500">
                    <div className="flex items-center gap-3">
                        {author && (
                            <span className="flex items-center gap-1">
                                <User size={12} />
                                {author.name?.split(" ")[0] || author}
                            </span>
                        )}
                        <span className="flex items-center gap-1">
                            <Calendar size={12} />
                            {formatDate(published_at || created_at, "short")}
                        </span>
                    </div>

                    {estimatedReadTime > 0 && (
                        <span className="flex items-center gap-1 text-slate-400">
                            <Clock size={12} />
                            {estimatedReadTime}m
                        </span>
                    )}
                </div>
            </div>
        </article>
    )
}

export default PostCard
