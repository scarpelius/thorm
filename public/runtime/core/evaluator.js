import { exprHandlers, opHandlers } from './handlers/handlers.generated.js';

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
    const handler = exprHandlers[expr.k];
    if (!handler) throw new Error('Unknown expr kind '+expr.k+' '+ expr);
    return handler(expr, e, ctx, { evaluate, getByPath, toBool, atoms, opHandlers });
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
