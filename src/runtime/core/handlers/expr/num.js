export function num(expr, e, ctx, helpers) {
  return Number(helpers.evaluate(expr.x, e, ctx));
}

