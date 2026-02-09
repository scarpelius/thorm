export function array_keys(a) {
  return (a && typeof a === 'object') ? Object.keys(a) : [];
}

