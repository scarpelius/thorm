export function concat(expr, e, ctx, helpers) {
  return expr.parts.map(p => helpers.evaluate(p, e, ctx)).join('');
}

