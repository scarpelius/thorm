export function currentPath(base = '/') {
  let p = location.pathname;
  if (base !== '/' && p.startsWith(base)) p = p.slice(base.length) || '/';
  if (p.length > 1 && p.endsWith('/')) p = p.slice(0, -1);
  return p || '/';
}
export function breadcrumbsFromPath(path = currentPath()) {
  if (!path) return [];
  let p = path.startsWith('/') ? path : `/${path}`;
  p = p.replace(/\/+/g, '/');
  if (p.length > 1 && p.endsWith('/')) p = p.slice(0, -1);
  if (p === '/') return [{ id: 1, label: 'Home', href: '/' }];

  const parts = p.split('/').filter(Boolean);
  let href = '';
  let id = 1;
  const crumbs = [{ id: id++, label: 'Home', href: '/' }];
  for (const seg of parts) {
    href += `/${seg}`;
    let label;
    try { label = decodeURIComponent(seg); } catch { label = seg; }
    label = label.replace(/[-_]+/g, ' ');
    if (label) label = label[0].toUpperCase() + label.slice(1);
    crumbs.push({ id: id++, label, href });
  }
  return crumbs;
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
