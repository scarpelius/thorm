export function prop(expr, e, ctx, helpers) {
  if (!ctx || !ctx.props) return undefined;

  const name = expr.name;
  const bag = ctx.props;

  const hasOP = Object.prototype.hasOwnProperty.call(bag, name);
  if (!hasOP) return undefined;

  const v = bag[name];

  if (v && typeof v === 'object' && v.k) {
    return helpers.evaluate(v, e, ctx);
  }

  if (v === undefined && expr.fallback) {
    return helpers.evaluate(expr.fallback, e, ctx);
  }

  return v;
}

