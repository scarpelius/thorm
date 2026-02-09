export function max(a, b) {
  if (b == null && Array.isArray(a)) return Math.max(...a);
  return Math.max(Number(a), Number(b));
}

