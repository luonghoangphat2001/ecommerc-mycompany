import React from "react"
import { List, Grid3X3, Newspaper } from "lucide-react"
import PostCard from "./PostCard"
import Skeleton from "../../../components/common/Skeleton"
import useSettingsStore from "../../../store/useSettingsStore"

const PostList = ({ posts, loading, layout = "grid", columns = 3, showFeatured = true, onLayoutChange }) => {
    const translate = useSettingsStore((state) => state.translate)

    if (loading) {
        return (
            <div
                className={`grid gap-6 ${
                    columns === 2
                        ? "grid-cols-1 md:grid-cols-2"
                        : columns === 4
                        ? "grid-cols-1 md:grid-cols-2 lg:grid-cols-4"
                        : "grid-cols-1 md:grid-cols-2 lg:grid-cols-3"
                }`}
            >
                {Array.from({ length: 6 }).map((_, index) => (
                    <Skeleton key={index} className="h-72 rounded-xl" />
                ))}
            </div>
        )
    }

    if (!posts || posts.length === 0) {
        return (
            <div className="bg-white border border-slate-200/90 rounded-xl text-center py-16 shadow-card">
                <div className="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <Newspaper size={24} />
                </div>
                <p className="text-slate-600 text-sm font-medium">
                    {translate?.("no_posts") || "No articles have been published yet."}
                </p>
            </div>
        )
    }

    const featuredPost = showFeatured && layout === "grid" ? posts.find((p) => p.is_featured) : null
    const regularPosts = featuredPost ? posts.filter((p) => p.id !== featuredPost.id) : posts

    if (layout === "grid") {
        return (
            <div className="space-y-6">
                {onLayoutChange && (
                    <div className="flex items-center justify-between">
                        <p className="text-slate-500 text-xs font-semibold tabular-nums">
                            {translate?.("blog.articles_count", { count: posts.length }) || `${posts.length} articles`}
                        </p>
                        <div className="flex items-center gap-1.5">
                            <button
                                type="button"
                                onClick={() => onLayoutChange("grid")}
                                className={`p-2 rounded-lg transition-colors ${
                                    layout === "grid"
                                        ? "bg-[#004687] text-white"
                                        : "bg-white border border-slate-200 text-slate-600 hover:bg-slate-50"
                                }`}
                            >
                                <Grid3X3 size={16} />
                            </button>
                            <button
                                type="button"
                                onClick={() => onLayoutChange("list")}
                                className={`p-2 rounded-lg transition-colors ${
                                    layout === "list"
                                        ? "bg-[#004687] text-white"
                                        : "bg-white border border-slate-200 text-slate-600 hover:bg-slate-50"
                                }`}
                            >
                                <List size={16} />
                            </button>
                        </div>
                    </div>
                )}

                {featuredPost && (
                    <div>
                        <PostCard post={featuredPost} variant="horizontal" />
                    </div>
                )}

                <div
                    className={`grid gap-6 ${
                        columns === 2
                            ? "grid-cols-1 md:grid-cols-2"
                            : columns === 4
                            ? "grid-cols-1 md:grid-cols-2 lg:grid-cols-4"
                            : "grid-cols-1 md:grid-cols-2 lg:grid-cols-3"
                    }`}
                >
                    {regularPosts.map((post) => (
                        <PostCard key={post.id} post={post} variant="default" />
                    ))}
                </div>
            </div>
        )
    }

    if (layout === "list") {
        return (
            <div className="space-y-5">
                {posts.map((post) => (
                    <PostCard key={post.id} post={post} variant="horizontal" />
                ))}
            </div>
        )
    }

    if (layout === "compact") {
        return (
            <div className="space-y-3">
                {posts.slice(0, 5).map((post) => (
                    <PostCard key={post.id} post={post} variant="compact" />
                ))}
            </div>
        )
    }

    return (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {posts.map((post) => (
                <PostCard key={post.id} post={post} />
            ))}
        </div>
    )
}

export default PostList
