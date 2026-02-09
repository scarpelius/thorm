export function cond(a, b, c, meta) {
  const { expr, e, ctx, evaluate } = meta;
  const test = evaluate(expr.a ?? expr.a, e, ctx);
  return !!test
    ? evaluate(expr.b ?? expr.b, e, ctx)
    : evaluate(expr.c ?? expr.c, e, ctx);
}

