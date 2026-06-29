import { useQuery } from '@tanstack/react-query';
import postApi from '../services/postApi';

export const usePostList = (params = {}) => {
  return useQuery({
    queryKey: ['posts', params],
    queryFn: () => postApi.getPosts(params),
    staleTime: 5 * 60 * 1000,
  });
};

export const useFeaturedPosts = () => {
  return useQuery({
    queryKey: ['featured-posts'],
    queryFn: () => postApi.getFeaturedPosts(),
    staleTime: 10 * 60 * 1000,
  });
};

export const usePostsByCategory = (categoryId) => {
  return useQuery({
    queryKey: ['posts-category', categoryId],
    queryFn: () => postApi.getPostsByCategory(categoryId),
    enabled: !!categoryId,
    staleTime: 5 * 60 * 1000,
  });
};
