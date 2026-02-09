export function min(a, b) {
  if (b == null && Array.isArray(a)) return Math.min(...a);
  return Math.min(Number(a), Number(b));
}

