export function normalizeProps(irProps) {
  const out = { attrs: [], cls: null, on: [], style: [] };

  if (Array.isArray(irProps)) {
    for (const p of irProps) {
      if (!Array.isArray(p)) continue;
      const [kind, a, b] = p;
      if (kind === 'attrs' && a && typeof a === 'object')
        out.attrs.push(...Object.entries(a));
      else if (kind === 'cls')
        out.cls = a;
      else if (kind === 'style' && a && typeof a === 'object')
        out.style.push(...Object.entries(a));
      else if (kind === 'on')
        out.on.push([a, b]); // [event, action]
    }
  } else if (irProps && typeof irProps === 'object') {
    // object-ish: { attrs:{}, cls: expr|string, on:{click:action}, style:{} }
    if (irProps.attrs && typeof irProps.attrs === 'object')
      out.attrs.push(...Object.entries(irProps.attrs));
    if (irProps.cls != null)
      out.cls = irProps.cls;
    if (irProps.style && typeof irProps.style === 'object')
      out.style.push(...Object.entries(irProps.style));
    if (irProps.on && typeof irProps.on === 'object')
      out.on.push(...Object.entries(irProps.on)); // [[event, action], ...]
  }
  return out;
}

export function normalizeSlotsMap(m) {
  const out = {};
  if (!m) return out;
  for (const [k,v] of Object.entries(m)) 
    out[k || 'default'] = Array.isArray(v) ? v : [];
  return out;
}

export function exprContainsProp(expr) {
  if (!expr || typeof expr !== 'object') return false;
  switch (expr.k) {
    case 'prop': return true;
    case 'concat': return Array.isArray(expr.parts) && expr.parts.some(exprContainsProp);
    case 'op': return exprContainsProp(expr.a) || exprContainsProp(expr.b) || exprContainsProp(expr.c);
    case 'get': return exprContainsProp(expr.a) || exprContainsProp(expr.b);
    default: return false;
  }
}

export function resolveProps(expr, ctx) {
  if (!expr || typeof expr !== 'object') return expr;
  if (expr.k === 'prop') {
    const ex = ctx && ctx.__propsExpr ? ctx.__propsExpr[expr.name] : undefined;
    // Fallback to a stable "val(undefined)" if prop missing
    return ex ? resolveProps(ex, ctx) : { k: 'val', v: undefined };
  }
  // Recurse through common shapes
  switch (expr.k) {
    case 'concat':
      return { ...expr, parts: expr.parts.map(p => resolveProps(p, ctx)) };
    case 'op':
      return { ...expr,
        a: resolveProps(expr.a, ctx),
        b: resolveProps(expr.b, ctx),
        c: resolveProps(expr.c, ctx),
      };
    case 'get':
      return { ...expr,
        a: resolveProps(expr.a, ctx),
        b: resolveProps(expr.b, ctx),
      };
    // add other nodes you use in text if needed (num/str are fine)
    default:
      return expr;
  }
}
