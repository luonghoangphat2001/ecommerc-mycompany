import useSettingsStore from '../../../store/useSettingsStore';
import React from "react"
import { Link } from "react-router-dom"
import { Calendar, User, Clock, Tag, ArrowRight, Eye } from "lucide-react"
import { formatDate } from "../../../utils/date"
import { truncateText } from "../../../utils/formatters"

const PostCard = ({ post, variant = "default" }) => {
    const translate = useSettingsStore(state => state.translate);

    if (!post) return null

    const { title, slug, excerpt, content, author, tags = [], published_at, created_at, view_count = 0, read_time, is_featured = false } = post

    // Use image_url from API (like Product), fallback to image object url
    const postImage = post.image_url || post.image?.url || post.featured_image || null
    const postCategory = post.category || post.categories?.[0]

    // Calculate read time if not provided (rough estimate: 200 words per minute)
    const estimatedReadTime = read_time || Math.ceil((content?.split(/\s+/)?.length || 0) / 200)

    // Card variants
    const isHorizontal = variant === "horizontal"
    const isCompact = variant === "compact"

    if (isCompact) {
        return (
            <Link to={`/posts/${slug}`} className="flex gap-4 p-4 bg-white rounded-xl hover:bg-slate-50 transition-colors group">
                {postImage ? (
                    <img
                        src={postImage}
                        alt={title}
                        className="w-20 h-20 object-cover rounded-lg flex-shrink-0"
                        onError={(e) => {
                            e.target.style.display = "none"
                        }}
                    />
                ) : (
                    <div className="w-20 h-20 bg-gradient-to-br from-blue-100 to-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span className="text-2xl">📝</span>
                    </div>
                )}
                <div className="flex-1 min-w-0">
                    {postCategory && <span className="text-xs text-blue-600 font-medium">{postCategory.name}</span>}
                    <h4 className="font-semibold text-slate-800 line-clamp-2 group-hover:text-blue-600 transition-colors">{title}</h4>
                    <div className="flex items-center gap-3 mt-2 text-xs text-slate-500">
                        <span className="flex items-center gap-1">
                            <Calendar size={12} />
                            {formatDate(published_at || created_at)}
                        </span>
                    </div>
                </div>
            </Link>
        )
    }

    if (isHorizontal) {
        return (
            <div className="flex flex-col md:flex-row bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 group">
                {/* Image */}
                <div className="md:w-2/5 relative overflow-hidden">
                    {postImage ? (
                        <img
                            src={postImage}
                            alt={title}
                            className="w-full h-64 md:h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            onError={(e) => {
                                e.target.style.display = "none"
                                e.target.nextSibling.style.display = "flex"
                            }}
                        />
                    ) : null}
                    <div className={`w-full h-64 md:h-full bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center ${postImage ? "hidden" : ""}`}>
                        <span className="text-6xl">📝</span>
                    </div>
                    {is_featured && <span className="absolute top-4 left-4 px-3 py-1 bg-red-500 text-white text-xs font-bold rounded-full">{translate?.("featured") || "Nổi bật"}</span>}
                </div>

                {/* Content */}
                <div className="md:w-3/5 p-6 md:p-8 flex flex-col">
                    {/* Category */}
                    {postCategory && (
                        <Link to={`/posts?category=${postCategory.slug}`} className="text-sm text-blue-600 font-medium hover:text-blue-700 mb-3">
                            {postCategory.name}
                        </Link>
                    )}

                    {/* Title */}
                    <h3 className="text-2xl font-bold text-slate-900 mb-3 group-hover:text-blue-600 transition-colors">
                        <Link to={`/posts/${slug}`}>{title}</Link>
                    </h3>

                    {/* Excerpt */}
                    <p className="text-slate-600 mb-4 line-clamp-3">{truncateText(excerpt || content, 200)}</p>

                    {/* Tags */}
                    {tags.length > 0 && (
                        <div className="flex flex-wrap gap-2 mb-4">
                            {tags.slice(0, 3).map((tag) => (
                                <span key={tag.id || tag} className="px-2 py-1 bg-slate-100 text-slate-600 text-xs rounded-lg">
                                    <Tag size={10} className="inline mr-1" />
                                    {tag.name || tag}
                                </span>
                            ))}
                            {tags.length > 3 && <span className="px-2 py-1 text-slate-400 text-xs">+{tags.length - 3}</span>}
                        </div>
                    )}

                    {/* Meta */}
                    <div className="mt-auto flex items-center justify-between pt-4 border-t border-slate-100">
                        <div className="flex items-center gap-4 text-sm text-slate-500">
                            {author && (
                                <span className="flex items-center gap-1.5">
                                    <User size={14} />
                                    {author.name || author}
                                </span>
                            )}
                            <span className="flex items-center gap-1.5">
                                <Calendar size={14} />
                                {formatDate(published_at || created_at)}
                            </span>
                            {estimatedReadTime > 0 && (
                                <span className="flex items-center gap-1.5">
                                    <Clock size={14} />
                                    {estimatedReadTime} {translate?.("min_read") || "phút đọc"}
                                </span>
                            )}
                        </div>

                        <Link to={`/posts/${slug}`} className="flex items-center gap-1 text-blue-600 font-medium hover:gap-2 transition-all">
                            {translate?.("read_more") || "Đọc tiếp"}
                            <ArrowRight size={16} />
                        </Link>
                    </div>
                </div>
            </div>
        )
    }

    // Default card layout
    return (
        <article className="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group">
            {/* Image */}
            <div className="relative aspect-video overflow-hidden">
                {postImage ? (
                    <img
                        src={postImage}
                        alt={title}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        onError={(e) => {
                            e.target.style.display = "none"
                            e.target.nextSibling.style.display = "flex"
                        }}
                    />
                ) : null}
                <div className={`w-full h-full bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center ${postImage ? "hidden" : ""}`}>
                    <span className="text-6xl">📝</span>
                </div>

                {/* Category Badge */}
                {postCategory && (
                    <Link to={`/posts?category=${postCategory.slug}`} className="absolute top-4 left-4 px-3 py-1 bg-white/90 backdrop-blur-sm text-blue-600 text-xs font-semibold rounded-full hover:bg-white transition-colors">
                        {postCategory.name}
                    </Link>
                )}

                {/* Featured Badge */}
                {is_featured && <span className="absolute top-4 right-4 px-3 py-1 bg-red-500 text-white text-xs font-bold rounded-full">{translate?.("featured") || "Nổi bật"}</span>}

                {/* View Count */}
                {view_count > 0 && (
                    <div className="absolute bottom-4 left-4 flex items-center gap-1 px-2 py-1 bg-black/50 text-white text-xs rounded-lg">
                        <Eye size={12} />
                        {view_count.toLocaleString()}
                    </div>
                )}
            </div>

            {/* Content */}
            <div className="p-6">
                {/* Title */}
                <h3 className="text-xl font-bold text-slate-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">
                    <Link to={`/posts/${slug}`}>{title}</Link>
                </h3>

                {/* Excerpt */}
                <p className="text-slate-600 text-sm mb-4 line-clamp-2">{truncateText(excerpt || content, 120)}</p>

                {/* Tags */}
                {tags.length > 0 && (
                    <div className="flex flex-wrap gap-2 mb-4">
                        {tags.slice(0, 3).map((tag, index) => (
                            <span key={tag.id || tag || index} className="px-2 py-1 bg-slate-100 text-slate-600 text-xs rounded">
                                #{tag.name || tag}
                            </span>
                        ))}
                    </div>
                )}

                {/* Meta */}
                <div className="flex items-center justify-between pt-4 border-t border-slate-100">
                    <div className="flex items-center gap-3 text-xs text-slate-500">
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
                        <span className="flex items-center gap-1 text-xs text-slate-400">
                            <Clock size={12} />
                            {estimatedReadTime} {translate?.("min_read") || "phút"}
                        </span>
                    )}
                </div>
            </div>
        </article>
    )
}

export default PostCard
