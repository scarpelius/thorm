// /runtime/primitives/LinkMount.js

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
 *              - http:  HTTP action API (performHttp()) — not used here
 *              - ctx:   per-subtree evaluation context (e.g., { item }, { params, query })
 *   registry → PrimitiveRegistry used to mount child IR nodes.
 *
 * Lifecycle:
 *   mount()   → called once when inserted into DOM
 *   update()  → (optional) called when IR changes but node stays mounted
 *   dispose() → called before removal; must release listeners, children, scopes, etc.
 *
 * Primitive description:
 *   This primitive renders an anchor element (<a>) with a reactive `href` derived
 *   from `ir.to`. On click it performs client-side navigation by pushing a new
 *   history entry and invoking `window.__THORM_REROUTE__()` (router refresh). It also
 *   supports dynamic class/attrs and arbitrary child nodes.
 */

// -----------------------
// Private file-local helpers
// -----------------------

function setAttr(el, name, value) {
  if (value == null || value === false) { el.removeAttribute(name); return; }
  if (value === true) { el.setAttribute(name, ''); return; }
  el.setAttribute(name, String(value));
}

import { normalizeProps } from './../utils/props.js';

/**
 * @implements {Mountable}
 */
export default class LinkMount {
  /**
   * @param {Node} parent
   * @param {Object} ir                   // shape: { k:'link', to: Expr, replace?: boolean|Expr, cls?: Expr, attrs?: [*, name, expr][], children?: IR[] }
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

    this.a = null;
    this.listeners = [];
    this.childInstances = [];
  }

  mount() {
    const hydration = this.services.hydrate;
    const cursor = hydration?.cursor;
    if (hydration?.active && cursor) {
      this.a = cursor.nextElement(this.parent, 'a');
    } else {
      this.a = document.createElement('a');
      this.parent.appendChild(this.a);
    }
    const a = this.a;
    const { attrs, cls, style, on } = normalizeProps(this.ir.props);

    // -----------------------
    // Reactive HREF
    // -----------------------
    const applyHref = () => {
      const to = this.evalr.evaluate(this.ir.to, null, this.ctx);
      a.setAttribute('href', typeof to === 'string' ? to : String(to ?? ''));
    };
    this.evalr.bindReactive(this.ir.to, applyHref, this.scope);

    // -----------------------
    // Optional replace flag (Expr or boolean)
    // -----------------------
    const getReplace = (e) => {
      const raw = this.ir.replace;
      if (raw == null) return false;
      if (typeof raw === 'boolean') return raw;
      return !!this.evalr.evaluate(raw, e, this.ctx);
    };

    // -----------------------
    // Dynamic CLASS binding
    // -----------------------
    if (cls) {
      const applyCls = () => { a.className = String(this.evalr.evaluate(cls, null, this.ctx) ?? ''); };
      this.evalr.bindReactive(cls, applyCls, this.scope);
    }

    // -----------------------
    // Additional ATTRS (optional)
    // -----------------------
    for (const p of (this.ir.attrs || this.ir.props?.attrs || [])) {
      const name = p[1], expr = p[2];
      const apply = () => setAttr(a, name, this.evalr.evaluate(expr, null, this.ctx));
      this.evalr.bindReactive(expr, apply, this.scope);
    }

    // -----------------------
    // CLICK → client-side navigate
    //   - prevent default
    //   - compute `to` via evaluate(ir.to, e, ctx)
    //   - push/replace state
    //   - trigger router refresh via window.__THORM_REROUTE__()
    // -----------------------
    const onClick = (e) => {
      // Let modifier-clicks do normal browser behavior (open new tab, etc.)
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;

      e.preventDefault();

      const to = this.evalr.evaluate(this.ir.to, e, this.ctx) ?? '/';
      const replace = getReplace(e);

      if (typeof to === 'string' && to) {
        if (replace) history.replaceState(null, '', to);
        else history.pushState(null, '', to);

        // Router hook as in legacy runtime
        if (typeof window.__THORM_REROUTE__ === 'function') window.__THORM_REROUTE__();
      }
    };
    a.addEventListener('click', onClick);
    this.listeners.push(['click', onClick]);

    // -----------------------
    // CHILDREN
    // -----------------------
    for (const child of (this.ir.children || [])) {
      const inst = this.registry.mount(a, child, this.scope, {
        ...this.services,
        ctx: this.ctx
      });
      this.childInstances.push(inst);
    }
  }

  update(nextIr) {
    this.ir = nextIr;
    // Reactive bindings already cover updates; nothing else to do here.
  }

  dispose() {
    for (const [ev, fn] of this.listeners) this.a.removeEventListener(ev, fn);
    this.listeners.length = 0;

    for (const inst of this.childInstances) inst.dispose?.();
    this.childInstances.length = 0;
  }
}
