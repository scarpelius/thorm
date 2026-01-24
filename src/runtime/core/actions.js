function evalMaybe(evalr, evt, ctx, v) {
  return (v && typeof v === 'object' && v.k) ? evalr.evaluate(v, evt, ctx) : v;
}

function parseSource(source) {
  if (typeof source !== 'string') return null;
  if (source.startsWith('local://')) {
    return { scheme: 'local', key: source.slice('local://'.length) };
  }
  if (source.startsWith('cookie://')) {
    return { scheme: 'cookie', key: source.slice('cookie://'.length) };
  }
  if (source.startsWith('https://') || source.startsWith('http://')) {
    return { scheme: 'http', url: source };
  }
  return null;
}

function parseValue(raw) {
  if (raw == null) return null;
  if (typeof raw !== 'string') return raw;
  try { return JSON.parse(raw); } catch { return raw; }
}

async function readSource(source) {
  const parsed = parseSource(source);
  if (!parsed) return null;
  if (parsed.scheme === 'local') {
    return parseValue(localStorage.getItem(parsed.key));
  }
  if (parsed.scheme === 'cookie') {
    const cookies = document.cookie ? document.cookie.split(';') : [];
    for (const c of cookies) {
      const [k, ...rest] = c.trim().split('=');
      if (k === parsed.key) {
        return parseValue(decodeURIComponent(rest.join('=')));
      }
    }
    return null;
  }
  if (parsed.scheme === 'http') {
    try {
      const res = await fetch(parsed.url, { credentials: 'same-origin' });
      if (!res.ok) return null;
      const text = await res.text();
      return parseValue(text);
    } catch {
      return null;
    }
  }
  return null;
}

function writeSource(source, value) {
  const parsed = parseSource(source);
  if (!parsed) return;
  const out = typeof value === 'string' ? value : JSON.stringify(value);
  if (parsed.scheme === 'local') {
    localStorage.setItem(parsed.key, out);
    return;
  }
  if (parsed.scheme === 'cookie') {
    document.cookie = `${parsed.key}=${encodeURIComponent(out)}; path=/`;
  }
}

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
      a.v = evalMaybe(evalr, evt, ctx, action.to);
      evalr.notify(action.atom);
      break;
    }
    case 'add': {
      const a = evalr.atoms.get(action.atom);
      const by = evalMaybe(evalr, evt, ctx, action.by);
      a.v = Number(a.v) + Number(by ?? 0);
      evalr.notify(action.atom);
      break;
    }
    case 'http': {
      await services.http?.performHttp?.(action, evt, ctx);
      break;
    }
    case 'navigate': {
      const to = evalMaybe(evalr, evt, ctx, action.to);
      if (typeof to === 'string' && to) {
        history.pushState(null, '', to);
        if (typeof window.__THORM_REROUTE__ === 'function') window.__THORM_REROUTE__();
      }
      break;
    }
    case 'redirect': {
      const to = evalMaybe(evalr, evt, ctx, action.to);
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
      const val = evalMaybe(evalr, evt, ctx, action.value);

      const arr = Array.isArray(a.v) ? a.v : (a.v == null ? [] : [a.v]);
      a.v = [...arr, val];
      evalr.notify(action.atom);
      break;
    }
    case 'persist': {
      const a = evalr.atoms.get(action.atom);
      const source = evalMaybe(evalr, evt, ctx, action.source);
      if (typeof source === 'string') {
        writeSource(source, a?.v);
      }
      break;
    }
    case 'hydrate': {
      const a = evalr.atoms.get(action.atom);
      const source = evalMaybe(evalr, evt, ctx, action.source);
      const fallback = evalMaybe(evalr, evt, ctx, action.default);
      if (typeof source === 'string') {
        const v = await readSource(source);
        if (v !== null && v !== undefined) {
          a.v = v;
          evalr.notify(action.atom);
        } else if (fallback !== undefined) {
          a.v = fallback;
          evalr.notify(action.atom);
        }
      }
      break;
    }
    case 'cap': {
      try {
        const args = evalMaybe(evalr, evt, ctx, action.args);
        const { invokeCapability } = await import('../capabilities/invoke.js');

        const value = await invokeCapability(action.name, args);
        if (action.to != null) {
          const a = evalr.atoms.get(action.to);
          a.v = value;
          evalr.notify(action.to);
        }
      } catch (e) {
        if (action.error != null) {
          const a = evalr.atoms.get(action.error);
          a.v = String(e?.message ?? e);
          evalr.notify(action.error);
        }
        if (services.dev) console.warn('[actions] cap failed', action, e);
      }
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
