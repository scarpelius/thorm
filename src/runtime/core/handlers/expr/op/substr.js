export function substr(a, b, c) {
  const s = String(a ?? '');
  const start = Number(b ?? 0);
  if (c == null) return s.slice(start);
  const len = Number(c);
  return s.slice(start, start + len);
}

