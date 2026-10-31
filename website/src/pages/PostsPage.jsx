import React from "react"
import PostList from "../features/post/components/PostList"
import { usePostList } from "../features/post/hooks/usePostList"
import useSettingsStore from "../store/useSettingsStore"
import { PageHeading } from "../components/common"

const PostsPage = () => {
    const translate = useSettingsStore((state) => state.translate)
    const { data: postsData, isLoading } = usePostList({ per_page: 12 })
    const posts = postsData?.data?.data || postsData?.data || []

    return (
        <div className="w-full">
            <div className="mb-6 pb-5 border-b border-slate-200/90">
                <PageHeading size="lg">{translate("header.blog") || "News & Editorial Insights"}</PageHeading>
                <p className="text-sm text-slate-500 mt-1">
                    Product guides, industry updates, and announcements from our editorial team.
                </p>
            </div>
            <PostList posts={posts} loading={isLoading} />
        </div>
    )
}

export default PostsPage
