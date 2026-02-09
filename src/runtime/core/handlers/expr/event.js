export function event(expr, e, ctx, helpers) {
  return helpers.getByPath(e, expr.path);
}

