export function implode(a, b) {
  return Array.isArray(b) ? b.join(String(a ?? '')) : '';
}

