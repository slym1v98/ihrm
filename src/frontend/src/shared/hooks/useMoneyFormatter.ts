'use client';
import { useSettings } from '@/domains/config/hooks/useConfig';

function formatNumber(value: number, decimalSeparator: string, thousandsSeparator: string) {
  const sign = value < 0 ? '-' : '';
  const abs = Math.abs(value);
  const [integer, decimal] = abs.toFixed(2).split('.');
  const grouped = integer.replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSeparator);
  if (decimal === '00') return sign + grouped;
  return sign + grouped + decimalSeparator + decimal;
}

export function useMoneyFormatter() {
  const { data } = useSettings();
  const get = (key: string, fallback: string) => data?.find(s => s.key === key)?.value || fallback;
  const symbol = get('currency.symbol', 'đ');
  const position = get('currency.position', 'suffix');
  const decimalSeparator = get('currency.decimal_separator', ',');
  const thousandsSeparator = get('currency.thousands_separator', '.');

  return {
    symbol,
    position,
    decimalSeparator,
    thousandsSeparator,
    formatMoney(value: number | string | null | undefined) {
      if (value == null || value === '') return '';
      const n = Number(value);
      if (Number.isNaN(n)) return String(value);
      const amount = formatNumber(n, decimalSeparator, thousandsSeparator);
      return position === 'prefix' ? `${symbol}${amount}` : `${amount}${symbol}`;
    },
  };
}
