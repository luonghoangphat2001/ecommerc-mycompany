import React from "react"
import { useParams } from "react-router-dom"
import PostDetail from "../features/post/components/PostDetail"
import { usePostDetail } from "../features/post/hooks/usePostDetail"

const PostDetailPage = () => {
    const { slug } = useParams()
    const { data: postData, isLoading, error } = usePostDetail(slug)

    const post = postData?.data

    if (isLoading) {
        return (
            <div className="max-w-3xl mx-auto py-8">
                <div className="bg-white border border-slate-200/90 rounded-xl p-8 animate-pulse">
                    <div className="h-7 bg-slate-200 rounded w-3/4 mb-3"></div>
                    <div className="h-4 bg-slate-200 rounded w-1/3 mb-6"></div>
                    <div className="h-64 bg-slate-200 rounded-xl mb-6"></div>
                    <div className="space-y-3">
                        <div className="h-4 bg-slate-200 rounded"></div>
                        <div className="h-4 bg-slate-200 rounded"></div>
                        <div className="h-4 bg-slate-200 rounded w-5/6"></div>
                    </div>
                </div>
            </div>
        )
    }

    if (error || !post) {
        return (
            <div className="max-w-3xl mx-auto py-16 text-center bg-white border border-slate-200/90 rounded-xl">
                <p className="text-sm font-medium text-slate-500">Article not found.</p>
            </div>
        )
    }

    return <PostDetail post={post} />
}

export default PostDetailPage
