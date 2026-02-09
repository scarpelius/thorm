export function in_array(a, b) {
  return Array.isArray(b) ? b.includes(a) : false;
}

