import { useQuery } from '@tanstack/react-query';
import pageApi from '../services/pageApi';
import useSettingsStore from '../../../store/useSettingsStore';

export const useHomeData = () => {
  const language = useSettingsStore((state) => state.language);
  return useQuery({
    queryKey: ['home-content', language],
    queryFn: () => pageApi.getHomeContent(),
    staleTime: 10 * 60 * 1000,
  });
};

export const useAboutData = () => {
  const language = useSettingsStore((state) => state.language);
  return useQuery({
    queryKey: ['about-content', language],
    queryFn: () => pageApi.getAboutContent(),
    staleTime: 30 * 60 * 1000,
  });
};

export const usePageBySlug = (slug) => {
  const language = useSettingsStore((state) => state.language);
  return useQuery({
    queryKey: ['page', slug, language],
    queryFn: () => pageApi.getPageBySlug(slug),
    enabled: !!slug,
    staleTime: 30 * 60 * 1000,
  });
};
