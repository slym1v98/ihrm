'use client';
import { useSettings } from '@/domains/config/hooks/useConfig';
import { formatByPhpPattern } from '@/shared/lib/dateFormat';

export function useDateFormatter() {
  const { data } = useSettings();
  const get = (key: string, fallback: string) => data?.find(s => s.key === key)?.value || fallback;
  const dateFormat = get('locale.date_format', 'd/m/Y');
  const dateTimeFormat = get('locale.datetime_format', 'd/m/Y H:i:s');
  return {
    dateFormat,
    dateTimeFormat,
    formatDate: (value: string | Date | null | undefined) => formatByPhpPattern(value, dateFormat),
    formatDateTime: (value: string | Date | null | undefined) => formatByPhpPattern(value, dateTimeFormat),
  };
}
