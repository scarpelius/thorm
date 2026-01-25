export function createEvaluator(store) {
  const { atoms, ensureAtom, notify, subscribeMany } = store;

  function getByPath(obj, path) {
    if (!obj) return undefined;
    if (path === '') return obj;
    const parts = path.split('.');
    let current = obj;
    for (const p of parts){
        if (current == null) return undefined;
        current = current[p];
    }
    return current;
  }

  function deps(expr, out = new Set()) {
    if (!expr || typeof expr !== 'object') return out;
    switch (expr.k) {
      case 'read': out.add(expr.atom); break;
      case 'not':  deps(expr.x, out); break;
      case 'op':
        deps(expr.a, out); 
        deps(expr.b, out); 
        deps(expr.c, out);
        break;
      case 'concat':
        for (const p of expr.parts) deps(p, out); 
        break;
      case 'stringify':
        deps(expr.value, out);
        break;
      case 'val': break;
      case 'get': {
        // If IR ever emits top-level {k:'get', a, b} (rare), collect both sides.
        deps(expr.a, out);
        deps(expr.b, out);
        break;
      }

      default: break;
    }
    return out;
  }

  function evaluate(expr, e, ctx) {
    if (!expr || typeof expr !== 'object') return expr;
    switch (expr.k) {
      case 'val': return expr.v;
      case 'read': {
        const a = atoms.get(expr.atom);
        // If the atom isn’t registered, fail soft:
        // - For lists: [] is sensible
        // - Otherwise: undefined is OK 
        
        const ret = a ? a.v : (expr.expect === 'repeat' ? [] : undefined);
        if (expr.expect === 'repeat') return Array.isArray(ret) ? ret : (ret ?? []);
        if (typeof ret === 'object') return ret; // no implicit stringify
        //if(typeof ret === 'object') {
        //  return JSON.stringify(ret, null, 2)
        //}
        return ret;
      };
      case 'not': return !toBool(evaluate(expr.x, e));
      case 'event': return getByPath(e, expr.path);
      case 'num': return Number(evaluate(expr.x, e, ctx));
      case 'str': return String(evaluate(expr.x, e, ctx));
      case 'item':  return getByPath(ctx && ctx.item, expr.path);
      case 'op': {
        const a = evaluate(expr.a, e, ctx);
        const b = evaluate(expr.b, e, ctx);
        const c = evaluate(expr.c, e, ctx);
        switch (expr.name) {
          case 'add': return Number(a) + Number(b);
          case 'sub': return Number(a) - Number(b);
          case 'mul': return Number(a) * Number(b);
          case 'div': return Number(a) / Number(b);
          case 'mod': return Number(a) % Number(b);
          case 'eq': return a === b;
          case 'gt': return Number(a) > Number(b);
          case 'lt': return Number(a) < Number(b);
          case 'gte': return Number(a) >= Number(b);
          case 'lte': return Number(a) <= Number(b);
          case 'abs': return Math.abs(Number(a));
          case 'min': {
            if (b == null && Array.isArray(a)) return Math.min(...a);
            return Math.min(Number(a), Number(b));
          }
          case 'max': {
            if (b == null && Array.isArray(a)) return Math.max(...a);
            return Math.max(Number(a), Number(b));
          }
          case 'round': return Math.round(Number(a));
          case 'floor': return Math.floor(Number(a));
          case 'ceil': return Math.ceil(Number(a));
          case 'sqrt': return Math.sqrt(Number(a));
          case 'pow': return Math.pow(Number(a), Number(b));
          case 'trunc': return Math.trunc(Number(a));
          case 'sign': return Math.sign(Number(a));
          case 'log': return Math.log(Number(a));
          case 'log10': return Math.log10(Number(a));
          case 'log2': return Math.log2(Number(a));
          case 'exp': return Math.exp(Number(a));
          case 'strlen': return String(a ?? '').length;
          case 'substr': {
            const s = String(a ?? '');
            const start = Number(b ?? 0);
            if (c == null) return s.slice(start);
            const len = Number(c);
            return s.slice(start, start + len);
          }
          case 'strpos': return String(a ?? '').indexOf(String(b ?? ''));
          case 'str_replace': {
            const search = String(a ?? '');
            const replace = String(b ?? '');
            const subject = String(c ?? '');
            return subject.split(search).join(replace);
          }
          case 'strtolower': return String(a ?? '').toLowerCase();
          case 'strtoupper': return String(a ?? '').toUpperCase();
          case 'trim': return String(a ?? '').trim();
          case 'ltrim': return String(a ?? '').trimStart();
          case 'rtrim': return String(a ?? '').trimEnd();
          case 'explode': return String(b ?? '').split(String(a ?? ''));
          case 'implode': return Array.isArray(b) ? b.join(String(a ?? '')) : '';
          case 'in_array': return Array.isArray(b) ? b.includes(a) : false;
          case 'count': {
            if (Array.isArray(a)) return a.length;
            if (a && typeof a === 'object') return Object.keys(a).length;
            return 0;
          }
          case 'array_keys': return (a && typeof a === 'object') ? Object.keys(a) : [];
          case 'array_values': return (a && typeof a === 'object') ? Object.values(a) : [];
          case 'json_encode': return JSON.stringify(a);
          case 'parseInt': return Number.parseInt(String(a ?? ''), 10);
          case 'parseFloat': return Number.parseFloat(String(a ?? ''));
          case 'intval': return Number.parseInt(String(a ?? ''), 10);
          case 'floatval': return Number.parseFloat(String(a ?? ''));
          case 'boolval': return Boolean(a);
          case 'strval': return String(a ?? '');
          case 'is_numeric': return Number.isFinite(Number(a));
          case 'is_string': return typeof a === 'string';
          case 'is_array': return Array.isArray(a);
          case 'cond': {
            const test = evaluate(expr.a ?? expr.a, e, ctx);
            return !!test
              ? evaluate(expr.b ?? expr.b, e, ctx)
              : evaluate(expr.c ?? expr.c, e, ctx);
          }
          case 'get': {
            const obj = evaluate(expr.a, e, ctx);
            const key = evaluate(expr.b, e, ctx);
            if (obj == null) return undefined;
            const k = (typeof key === 'number') ? key : String(key);
            try { return obj[k]; } catch { return undefined; }
          }
          case 'navigate': {
            const to = evaluate(action.to, e, ctx) ?? "/";
            if (typeof to === 'string') {
              history.pushState(null, "", to);
              if (typeof window.__THORM_REROUTE__ === 'function') window.__THORM_REROUTE__();
            }
            break;
          }
          default: throw new Error('Unknown op '+expr.name);
        }
      }
      case 'concat': {
        return expr.parts.map(p => evaluate(p, e, ctx)).join('');
      }
      case 'param': return (ctx && ctx.route && ctx.route.params || {})[expr.name];
      case 'query': return (ctx && ctx.route && ctx.route.query  || {})[expr.name];
      case 'prop': {

        // fast guards
        if (!ctx || !ctx.props) return undefined;

        const name = expr.name;
        const bag  = ctx.props;

        // prototype-safe read (works even if bag is a plain {})
        const hasOP = Object.prototype.hasOwnProperty.call(bag, name);

        if (!hasOP) return undefined;

        const v = bag[name];

        // if prop value is an IR expression, evaluate it
        if (v && typeof v === 'object' && v.k) {
          return evaluate(v, e, ctx);
        }

        // optional fallback support: { k:"prop", name:"title", fallback:{k:"val",v:"Untitled"} }
        if (v === undefined && expr.fallback) {
          return evaluate(expr.fallback, e, ctx);
        }

        return v;
      }
      case 'stringify': {
        const space = Number(expr.space ?? 0) | 0;
        const v = (expr.value && typeof expr.value === 'object' && expr.k)
          ? evaluate(expr.value, e, ctx)
          : expr.value;
        //console.log(evaluate(expr.value, e, ctx));
        try {
          return JSON.stringify(v, null, space);
        } catch {
          // fallback if circular/unserializable: best-effort string
          try { return String(v); } catch { return ''; }
        }
      }
      default: throw new Error('Unknown expr kind '+expr.k+' '+ expr);
    }
  }

  function bindReactive(expr, apply, scope) {
    const d = deps(expr);
    apply(); 
    // if no deps no-op dispenser
    if (!d.size) { return () => {}; }

    return subscribeMany(d, () => apply(), scope);
  }

  //function scheduleFlush(inst) {
  //  if (inst.scheduled) return;
  //  inst.scheduled = true;
  //  queueMicrotask(() => {
  //    inst.scheduled = false;
  //    inst._flushPending(); // calls child.update(...) once
  //  });
  //}

  function toBool(v) {
    // Match the project’s existing truthiness policy; default to JS truthiness
    // but keep it explicit for clarity and future tweaks.
    return !!v;
  }

  return { atoms, ensureAtom, notify, subscribeMany, evaluate, deps, bindReactive, getByPath, toBool };
}
