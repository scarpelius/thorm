export function not(expr, e, ctx, helpers) {
  return !helpers.toBool(helpers.evaluate(expr.x, e, ctx));
}

