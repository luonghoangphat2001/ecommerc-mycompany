import React from "react"
import { Link } from "react-router-dom"
import { ArrowLeft, Calendar, User } from "lucide-react"
import { formatDate } from "../../../utils/date"
import { getLocalizedText } from "../../../utils/formatters"
import useSettingsStore from "../../../store/useSettingsStore"

const PostDetail = ({ post }) => {
    const translate = useSettingsStore((state) => state.translate)

    if (!post) return null

    const postImage = post.image_url || post.image?.url || post.featured_image
    const postCategory = post.category || post.categories?.[0]
    const postTitle = getLocalizedText(post.title)
    const postContent = getLocalizedText(post.content)
    const categoryName = getLocalizedText(postCategory?.name)

    return (
        <article className="max-w-3xl mx-auto">
            <Link
                to="/posts"
                className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-[#004687] mb-5 transition-colors"
            >
                <ArrowLeft size={15} />
                <span>{translate("back_to_posts") || "Back to Insights"}</span>
            </Link>

            <div className="bg-white border border-slate-200/90 rounded-xl p-6 sm:p-10 shadow-card">
                <header className="mb-6 pb-6 border-b border-slate-100">
                    {categoryName && (
                        <span className="inline-block text-xs text-[#004687] font-semibold uppercase tracking-wider mb-2">
                            {categoryName}
                        </span>
                    )}
                    <h1 className="text-2xl sm:text-3xl font-bold text-[#09253E] tracking-tight leading-snug mb-3">
                        {postTitle}
                    </h1>
                    <div className="flex items-center gap-4 text-xs text-slate-500 tabular-nums">
                        {post.author?.name && (
                            <span className="inline-flex items-center gap-1.5">
                                <User size={13} />
                                <span>{post.author.name}</span>
                            </span>
                        )}
                        <span className="inline-flex items-center gap-1.5">
                            <Calendar size={13} />
                            <span>{formatDate(post.published_at || post.created_at)}</span>
                        </span>
                    </div>
                </header>

                {postImage && (
                    <img
                        src={postImage}
                        alt={postTitle}
                        className="w-full h-64 sm:h-80 object-cover rounded-xl mb-6 border border-slate-200/70"
                        onError={(e) => {
                            e.target.style.display = "none"
                        }}
                    />
                )}

                <div
                    className="prose prose-slate max-w-none text-slate-700 leading-relaxed text-sm sm:text-base"
                    dangerouslySetInnerHTML={{ __html: postContent }}
                />
            </div>
        </article>
    )
}

export default PostDetail
