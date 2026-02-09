export function count(a) {
  if (Array.isArray(a)) return a.length;
  if (a && typeof a === 'object') return Object.keys(a).length;
  return 0;
}

