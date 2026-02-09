export function navigate(a, b, c, meta) {
  const { e, ctx, evaluate } = meta;
  const to = evaluate(action.to, e, ctx) ?? "/";
  if (typeof to === 'string') {
    history.pushState(null, "", to);
    if (typeof window.__THORM_REROUTE__ === 'function') window.__THORM_REROUTE__();
  }
}

