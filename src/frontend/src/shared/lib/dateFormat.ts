export const DATE_FORMAT_OPTIONS = ['d/m/Y','m/d/Y','Y-m-d','d-m-Y','d.m.Y'] as const;
export const DATETIME_FORMAT_OPTIONS = ['d/m/Y H:i:s','m/d/Y H:i:s','Y-m-d H:i:s','d-m-Y H:i:s','d.m.Y H:i:s'] as const;

export function formatByPhpPattern(value: string | Date | null | undefined, pattern = 'd/m/Y') {
  if (!value) return '';
  const date = value instanceof Date ? value : new Date(value);
  if (Number.isNaN(date.getTime())) return String(value);
  const pad = (n: number) => String(n).padStart(2, '0');
  const map: Record<string, string> = {
    d: pad(date.getDate()),
    m: pad(date.getMonth() + 1),
    Y: String(date.getFullYear()),
    H: pad(date.getHours()),
    i: pad(date.getMinutes()),
    s: pad(date.getSeconds()),
  };
  return pattern.replace(/[dmYHis]/g, token => map[token] ?? token);
}
