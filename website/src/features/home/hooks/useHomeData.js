import { useQuery } from '@tanstack/react-query';
import pageApi from '../services/pageApi';

export const useHomeData = () => {
  return useQuery({
    queryKey: ['home-content'],
    queryFn: () => pageApi.getHomeContent(),
    staleTime: 10 * 60 * 1000,
  });
};

export const useAboutData = () => {
  return useQuery({
    queryKey: ['about-content'],
    queryFn: () => pageApi.getAboutContent(),
    staleTime: 30 * 60 * 1000,
  });
};

export const usePageBySlug = (slug) => {
  return useQuery({
    queryKey: ['page', slug],
    queryFn: () => pageApi.getPageBySlug(slug),
    enabled: !!slug,
    staleTime: 30 * 60 * 1000,
  });
};
