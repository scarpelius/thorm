// Factory so we can inject evaluator + atoms utilities
export function createHttp({ atoms, evaluate, ensureAtom, notify }) {
  async function performHttp(action, e, ctx) {
    const ctrl = new AbortController();

    // URL + method
    const url = typeof action.url === 'object' && action.url
      ? String(evaluate(action.url, e, ctx))
      : String(action.url || '');
    const method = (action.method || 'GET').toUpperCase();

    // ---- Build request headers (BC: reqHeaders preferred; legacy 'headers' object still works)
    const reqHeadersSrc =
      (action.reqHeaders && typeof action.reqHeaders === 'object')
        ? action.reqHeaders
        : (action.headers && typeof action.headers === 'object' && !Array.isArray(action.headers))
          ? action.headers
          : null;

    const headers = {};
    if (reqHeadersSrc) {
      for (const k in reqHeadersSrc) {
        const v = reqHeadersSrc[k];
        headers[k] = (v && typeof v === 'object') ? evaluate(v, e, ctx) : v;
      }
    }

    // ---- Body (Expr or plain)
    let body = action.body;
    if (body && typeof body === 'object' && body.k) {
      body = evaluate(body, e, ctx);
    }

    const isPlainObjectBody = body != null
      && typeof body === 'object'
      && !(body instanceof FormData)
      && !(body instanceof Blob)
      && !(body instanceof ArrayBuffer)
      && !(body instanceof URLSearchParams);

    if (isPlainObjectBody) {
      if (!('Content-Type' in headers) && !('content-type' in headers)) {
        headers['Content-Type'] = 'application/json';
      }
      body = JSON.stringify(body);
    }


    // ---- Optional: mark status "in flight"
    if (typeof action.status === 'number') {
      ensureAtom(action.status, 0);
      atoms.get(action.status).v = 0;
      notify(action.status);
    }

    // ---- Fetch
    try {
      const res = await fetch(url, { method, headers, body, signal: ctrl.signal });

      // status
      if (typeof action.status === 'number') {
        atoms.get(action.status).v = res.status;
        notify(action.status);
      }

      // body → to
      if (typeof action.to === 'number') {
        let val = null;
        const parse = action.parse || 'json';
        if (parse === 'text') {
          try { val = await res.text(); } catch { val = null; }
        } else if (parse === 'json') {
          try { val = await res.json(); } catch { val = null; }
        } else {
          val = null;
        }
        atoms.get(action.to).v = val;
        notify(action.to);
      }

      // response headers → resHeaders (BC: or numeric legacy action.headers)
      const resHeadersId = (typeof action.resHeaders === 'number')
        ? action.resHeaders
        : (typeof action.headers === 'number' ? action.headers : null);

      if (typeof resHeadersId === 'number') {
        atoms.get(resHeadersId).v = Object.fromEntries(res.headers.entries());
        notify(resHeadersId);
      }
    } catch (err) {
      console.log(err);
      if (err && err.name === 'AbortError') return;
      if (typeof action.status === 'number') {
        atoms.get(action.status).v = -1; // network error
        notify(action.status);
      }
    }
  }

  return { performHttp };
}
