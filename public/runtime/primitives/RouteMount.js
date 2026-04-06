// /runtime/primitives/RouteMount.js

/**
 * ThormJS Primitive Interface (Informal Contract)
 * -----------------------------------------------
 * All class-based primitives must implement the following interface:
 *
 *   interface Mountable {
 *     constructor(parent: Node, ir: Object, scope: Scope, services: Services, registry: PrimitiveRegistry)
 *     mount(): void
 *     update?(nextIr: Object): void
 *     dispose(): void
 *   }
 *
 * Parameters:
 *   parent   → DOM Node where this primitive will attach itself.
 *   ir       → Intermediate Representation (IR) node of this primitive.
 *   scope    → Reactive lifetime scope (handles onDispose callbacks).
 *   services → Core services provided by runtime:
 *                { evalr, nav?, http?, ctx? }
 *              - evalr: evaluator with evaluate(), deps(), bindReactive()
 *              - nav:   navigation API (navigate(), currentPath(), parseQuery())
 *              - http:  HTTP action API (performHttp())
 *              - ctx:   per-subtree evaluation context (e.g., { item }, { params, query })
 *   registry → PrimitiveRegistry used to mount child IR nodes.
 *
 * Lifecycle:
 *   mount()   → called once when inserted into DOM
 *   update()  → (optional) called when IR changes but node stays mounted
 *   dispose() → called before removal; must release listeners, children, scopes, etc.
 *
 * Primitive description:
 *   This primitive acts as a client-side router. It selects one view from a
 *   routing table based on `location.pathname` (normalized by an optional `base`).
 *   It provides the matched `path`, `params`, and `query` to the rendered subtree
 *   via `ctx` and re-renders on `popstate`. It also exposes a global reroute hook
 *   (`window.__THORM_REROUTE__`) for programmatic navigations initiated elsewhere.
 */

// -----------------------
// Private file-local helpers
// -----------------------

function normalizePathname(base = '/') {
  let p = location.pathname || '/';
  let b = base || '/';
  if (b.length > 1 && b.endsWith('/')) b = b.slice(0, -1);
  if (b !== '/' && p.startsWith(b)) {
    p = p.slice(b.length) || '/';
    if (p[0] !== '/') p = `/${p}`;
  }
  if (p.length > 1 && p.endsWith('/')) p = p.slice(0, -1);
  return p || '/';
}

function parseQuery() {
  const q = {};
  const usp = new URLSearchParams(location.search);
  for (const [k, v] of usp) q[k] = v;
  return q;
}

function toRegExp(maybeSlashDelimited) {
  if (maybeSlashDelimited instanceof RegExp) return maybeSlashDelimited;
  if (typeof maybeSlashDelimited === 'string') {
    const s = maybeSlashDelimited.trim();
    if (s.startsWith('/') && s.endsWith('/') && s.length >= 2) {
      return new RegExp(s.slice(1, -1));
    }
    return new RegExp(s);
  }
  throw new Error('route: invalid pattern');
}

/**
 * @implements {Mountable}
 */
export default class RouteMount {
  /**
   * @param {Node} parent
   * @param {Object} ir                   // shape: { k:'route', base?: string, table: {re:string|RegExp, keys:string[]}[], views: IR[], fallback: IR }
   * @param {import('../core/scope.js').Scope} scope
   * @param {{ evalr:any, nav?:any, http?:any, ctx?:any }} services
   * @param {import('../core/registry.js').PrimitiveRegistry} registry
   */
  constructor(parent, ir, scope, services, registry) {
    this.parent = parent;
    this.ir = ir;
    this.scope = scope;
    this.evalr = services.evalr;
    this.nav = services.nav;
    this.http = services.http;
    this.ctx = services.ctx || {};
    this.services = services;
    this.registry = registry;

    this.base = ir.base || '/';
    this.start = null;
    this.end   = null;
    this.childScope = null;
    this.childInstance = null;
    this._hydrating = false;

    // Compile routing table once
    const table = ir.table || [];
    this.routes = table.map((t, i) => ({
      re: toRegExp(t.re),
      keys: Array.isArray(t.keys) ? t.keys : [],
      view: (ir.views || [])[i]
    }));

    // Global reroute hook support
    this.prevReroute = null;
    this.onPop = () => this._reroute();
  }

  mount() {
    const hydration = this.services.hydrate;
    const cursor = hydration?.cursor;
    if (hydration?.active && cursor) {
      this.start = cursor.nextComment(this.parent, 'route:start');
      this._hydrating = true;
    } else {
      this.start = document.createComment('route:start');
      this.end = document.createComment('route:end');
      this.parent.append(this.start, this.end);
    }

    // Install global reroute hook (legacy contract)
    this.prevReroute = typeof window.__THORM_REROUTE__ === 'function' ? window.__THORM_REROUTE__ : null;
    window.__THORM_REROUTE__ = () => this._reroute();

    window.addEventListener('popstate', this.onPop);
    this._reroute();
  }

  update(nextIr) {
    // If the route table changes at runtime, we could recompile.
    // For now, just replace IR (a full reroute will pick up changes).
    this.ir = nextIr;
  }

  dispose() {
    window.removeEventListener('popstate', this.onPop);

    // Restore prior reroute hook if we owned it
    if (window.__THORM_REROUTE__ === this._boundReroute) {
      window.__THORM_REROUTE__ = this.prevReroute || undefined;
    } else if (typeof window.__THORM_REROUTE__ === 'function' && !this.prevReroute) {
      // If someone else overwrote it, do not clobber.
    }

    this._unmountView();
  }

  // -----------------------
  // Core routing
  // -----------------------

  _reroute() {
    const path = normalizePathname(this.base);
    const query = parseQuery();

    for (const { re, keys, view } of this.routes) {
      const m = re.exec(path);
      if (!m) continue;

      const params = {};
      for (let i = 0; i < keys.length; i++) {
        params[keys[i]] = decodeURIComponent(m[i + 1] || '');
      }
      this._mountView(view, { path, params, query });
      return;
    }

    // Fallback if nothing matched
    this._mountView(this.ir.fallback, { path, params: {}, query });
  }

  _mountView(viewIr, routeCtx) {
    // Merge route context into subtree ctx
    const childCtx = {
      ...this.ctx,
      route: {
        path: routeCtx.path,
        params: routeCtx.params,
        query: routeCtx.query
        }
    };

    // Clear existing view
    this._unmountView();

    // Mount fresh view
    const hydration = this.services.hydrate;
    const cursor = hydration?.cursor;
    const useHydrate = hydration?.active && cursor;
    const mountParent = useHydrate ? this.parent : document.createDocumentFragment();
    this.childScope = this.scope.fork();
    this.childInstance = this.registry.mount(mountParent, viewIr, this.childScope, {
      ...this.services,
      ctx: childCtx
    });
    if (!useHydrate) this.end.before(mountParent);
    if (useHydrate && this._hydrating && !this.end) {
      this.end = cursor.nextComment(this.parent, 'route:end');
      this._hydrating = false;
    }
  }

  _unmountView() {
    if (this.childInstance) {
      this.childInstance.dispose?.();
      this.childInstance = null;
    }
    if (this.childScope) {
      this.childScope.dispose?.();
      this.childScope = null;
    }
    this._clearBetween();
  }

  // -----------------------
  // Private helpers
  // -----------------------

  _clearBetween() {
    if (!this.start || !this.end) return;
    let n = this.start.nextSibling;
    while (n && n !== this.end) {
      const next = n.nextSibling;
      n.remove();
      n = next;
    }
  }

  // Keep a bound reference if you want to compare for restore
  get _boundReroute() {
    return window.__THORM_REROUTE__;
  }
}
