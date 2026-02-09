export function get(a, b, c, meta) {
  const { expr, e, ctx, evaluate } = meta;
  const obj = evaluate(expr.a, e, ctx);
  const key = evaluate(expr.b, e, ctx);
  if (obj == null) return undefined;
  const k = (typeof key === 'number') ? key : String(key);
  try { return obj[k]; } catch { return undefined; }
}

