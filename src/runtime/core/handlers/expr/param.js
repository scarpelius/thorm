export function param(expr, e, ctx, helpers) {
  return (ctx && ctx.route && ctx.route.params || {})[expr.name];
}

