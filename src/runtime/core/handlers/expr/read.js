export function read(expr, e, ctx, helpers) {
  const { atoms } = helpers;
  const a = atoms.get(expr.atom);
  const ret = a ? a.v : (expr.expect === 'repeat' ? [] : undefined);
  if (expr.expect === 'repeat') return Array.isArray(ret) ? ret : (ret ?? []);
  if (typeof ret === 'object') return ret;
  return ret;
}

