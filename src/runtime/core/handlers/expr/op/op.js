export function op(expr, e, ctx, helpers) {
  const a = helpers.evaluate(expr.a, e, ctx);
  const b = helpers.evaluate(expr.b, e, ctx);
  const c = helpers.evaluate(expr.c, e, ctx);
  const fn = helpers.opHandlers[expr.name];
  if (!fn) throw new Error('Unknown op ' + expr.name);
  return fn(a, b, c, { expr, e, ctx, ...helpers });
}

