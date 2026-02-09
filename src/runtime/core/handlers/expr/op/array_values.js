export function array_values(a) {
  return (a && typeof a === 'object') ? Object.values(a) : [];
}

