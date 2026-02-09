export function item(expr, e, ctx, helpers) {
  return helpers.getByPath(ctx && ctx.item, expr.path);
}

