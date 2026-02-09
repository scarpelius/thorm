export function query(expr, e, ctx, helpers) {
  return (ctx && ctx.route && ctx.route.query || {})[expr.name];
}

