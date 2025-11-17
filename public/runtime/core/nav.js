export function currentPath(base = '/') {
  let p = location.pathname;
  if (base !== '/' && p.startsWith(base)) p = p.slice(base.length) || '/';
  if (p.length > 1 && p.endsWith('/')) p = p.slice(0, -1);
  return p || '/';
}
export function parseQuery() {
  const out = {}; const usp = new URLSearchParams(location.search);
  for (const [k, v] of usp) out[k] = v;
  return out;
}
export function navigate(to, { replace = false } = {}) {
  const url = typeof to === 'string' ? to : String(to);
  if (replace) history.replaceState({}, '', url); 
  else history.pushState({}, '', url);
  dispatchEvent(new PopStateEvent('popstate'));
}
