import { useQuery } from '@tanstack/react-query';
import postApi from '../services/postApi';
import useSettingsStore from '../../../store/useSettingsStore';

export const usePostDetail = (slug) => {
  const language = useSettingsStore((state) => state.language);
  return useQuery({
    queryKey: ['post', slug, language],
    queryFn: () => postApi.getPostBySlug(slug),
    enabled: !!slug,
    staleTime: 30 * 60 * 1000,
  });
};

export const usePostById = (id) => {
  const language = useSettingsStore((state) => state.language);
  return useQuery({
    queryKey: ['post-id', id, language],
    queryFn: () => postApi.getPostById(id),
    enabled: !!id,
    staleTime: 30 * 60 * 1000,
  });
};
