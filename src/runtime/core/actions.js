export async function runAction(evalr, services, ctx, action, evt = null) {
  if (!action || !action.k) return;
  switch (action.k) {
    case 'inc': {
      const a = evalr.atoms.get(action.atom);
      a.v = Number(a.v) + Number(action.by ?? 1);
      evalr.notify(action.atom);
      break;
    }
    case 'set': {
      const a = evalr.atoms.get(action.atom);
      a.v = typeof action.to === 'object' && action.to !== null
        ? evalr.evaluate(action.to, evt, ctx)
        : action.to;
      evalr.notify(action.atom);
      break;
    }
    case 'add': {
      const a = evalr.atoms.get(action.atom);
      const by = typeof action.by === 'object' && action.by !== null
        ? evalr.evaluate(action.by, evt, ctx)
        : action.by;
      a.v = Number(a.v) + Number(by ?? 0);
      evalr.notify(action.atom);
      break;
    }
    case 'http': {
      await services.http?.performHttp?.(action, evt, ctx);
      break;
    }
    case 'navigate': {
      const to = typeof action.to === 'object' && action.to !== null
        ? evalr.evaluate(action.to, evt, ctx)
        : action.to;
      if (typeof to === 'string' && to) {
        history.pushState(null, '', to);
        if (typeof window.__THORM_REROUTE__ === 'function') window.__THORM_REROUTE__();
      }
      break;
    }
    case 'redirect': {
      const to = typeof action.to === 'object' && action.to !== null
        ? evalr.evaluate(action.to, evt, ctx)
        : action.to;
        const replace = !!action.replace;
        if (replace) window.location.replace(to);
        else window.location.assign(to);
        break;
    }
    case 'delay': {
      const ms = Math.max(0, Number(action.ms || 0));
      await new Promise(r => setTimeout(r, ms));
      if (Array.isArray(action.actions)) {
        for (const a of action.actions) await runAction(evalr, services, ctx, a, evt);
      }
      break;
    }
    case 'task': {
      if (Array.isArray(action.actions)) {
        for (const a of action.actions) await runAction(evalr, services, ctx, a, evt);
      }
      break;
    }
    case 'push': {
      const a = evalr.atoms.get(action.atom);
      const val = (action.value && typeof action.value === 'object' && action.value !== null)
        ? evalr.evaluate(action.value, evt, ctx)
        : action.value;

      const arr = Array.isArray(a.v) ? a.v : (a.v == null ? [] : [a.v]);
      a.v = [...arr, val];
      evalr.notify(action.atom);
      break;
    }
    default: {
      if (services.dev) console.warn('[actions] unknown action', action);
    }
  }
}

export async function runActions(evalr, services, ctx, actions, evt = null) {
  for (const a of actions || []) {
    // eslint-disable-next-line no-await-in-loop
    await runAction(evalr, services, ctx, a, evt);
  }
}
