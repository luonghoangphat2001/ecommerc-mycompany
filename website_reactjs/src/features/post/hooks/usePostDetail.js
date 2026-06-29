import { useQuery } from '@tanstack/react-query';
import postApi from '../services/postApi';

export const usePostDetail = (slug) => {
  return useQuery({
    queryKey: ['post', slug],
    queryFn: () => postApi.getPostBySlug(slug),
    enabled: !!slug,
    staleTime: 30 * 60 * 1000,
  });
};

export const usePostById = (id) => {
  return useQuery({
    queryKey: ['post-id', id],
    queryFn: () => postApi.getPostById(id),
    enabled: !!id,
    staleTime: 30 * 60 * 1000,
  });
};
