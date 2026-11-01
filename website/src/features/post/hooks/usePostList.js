import { useQuery } from '@tanstack/react-query';
import postApi from '../services/postApi';
import useSettingsStore from '../../../store/useSettingsStore';

export const usePostList = (params = {}) => {
  const language = useSettingsStore((state) => state.language);
  return useQuery({
    queryKey: ['posts', params, language],
    queryFn: () => postApi.getPosts(params),
    staleTime: 5 * 60 * 1000,
  });
};

export const useFeaturedPosts = () => {
  const language = useSettingsStore((state) => state.language);
  return useQuery({
    queryKey: ['featured-posts', language],
    queryFn: () => postApi.getFeaturedPosts(),
    staleTime: 10 * 60 * 1000,
  });
};

export const usePostsByCategory = (categoryId) => {
  const language = useSettingsStore((state) => state.language);
  return useQuery({
    queryKey: ['posts-category', categoryId, language],
    queryFn: () => postApi.getPostsByCategory(categoryId),
    enabled: !!categoryId,
    staleTime: 5 * 60 * 1000,
  });
};
