export function stringify(expr, e, ctx, helpers) {
  const space = Number(expr.space ?? 0) | 0;
  const v = (expr.value && typeof expr.value === 'object' && expr.k)
    ? helpers.evaluate(expr.value, e, ctx)
    : expr.value;
  try {
    return JSON.stringify(v, null, space);
  } catch {
    try { return String(v); } catch { return ''; }
  }
}

