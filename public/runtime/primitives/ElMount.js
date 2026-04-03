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
 *   This primitive represents a regular DOM element with dynamic attributes,
 *   event bindings, and child nodes.
 */

// -----------------------
// Private file-local helpers
// -----------------------

function setAttr(el, name, value) {
  if (value == null || value === false) { el.removeAttribute(name); return; }
  if (value === true) { el.setAttribute(name, ''); return; }
  el.setAttribute(name, String(value));
}

function setStyle(el, name, value) {
  if (value == null || value === false) { el.style.removeProperty(name); return; }
  el.style.setProperty(name, String(value));
}

function preHandleEvent(e, event, action) {
  if (event === 'submit') {
    e.preventDefault();
    e.stopPropagation?.();
  }
  if (action && (action.k === 'nav' || action.k === 'navigate')) {
    e.preventDefault();
  }
}

import { normalizeProps, resolveProps } from './../utils/props.js';
import { runAction } from '../core/actions.js';

/**
 * @implements {Mountable}
 */
export default class ElMount {
  /**
   * @param {Node} parent
   * @param {Object} ir                   // shape: { k:'el', tag:string, props?:{attrs?:[],style?:[],cls?:Expr,on?:[]}, children?:IR[] }
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

    this.el = null;
    this.listeners = [];
    this.childInstances = [];
  }

  mount() {
    const hydration = this.services.hydrate;
    const cursor = hydration?.cursor;
    const isClientTarget = this.ir?.render?.target === 'client';
    const isHydrating = !!(hydration?.active && cursor);
    if (isHydrating) {
      this.el = cursor.nextElement(this.parent, this.ir.tag);
    } else {
      this.el = document.createElement(this.ir.tag);
      this.parent.appendChild(this.el);
    }
    const el = this.el;
    const { attrs, cls, style, on } = normalizeProps(this.ir.props);
    const hasStyleExpr = (
      this.ir.props?.style
      && typeof this.ir.props.style === 'object'
      && this.ir.props.style.k
    );

    // -----------------------
    // Dynamic ATTR bindings
    // IR supports array-of-tuples: [*, name, expr]
    // -----------------------
    //for (const p of (this.ir.props?.attrs || [])) {
    for(const [name, expr] of attrs) {
      const apply = () => setAttr(el, name, this.evalr.evaluate(expr, null, this.ctx));
      this.evalr.bindReactive(expr, apply, this.scope);
    }

    // -----------------------
    // Dynamic CLASS binding
    // -----------------------
    if (this.ir.props?.cls) {
      const expr = resolveProps(this.ir.props.cls, this.ctx);
      const apply = () => { 
        const className = String(this.evalr.evaluate(expr, null, this.ctx) ?? ''); 
        el.className = className;
      };
      this.evalr.bindReactive(expr, apply, this.scope);
    }

    // -----------------------
    // Dynamic STYLE binding
    // -----------------------
    if (!hasStyleExpr) {
      for (const [name, expr] of (style || [])) {
        const isExpr = expr && typeof expr === 'object' && expr.k;
        if (isExpr) {
          const apply = () => setStyle(el, name, this.evalr.evaluate(expr, null, this.ctx));
          this.evalr.bindReactive(expr, apply, this.scope);
        } else {
          setStyle(el, name, expr);
        }
      }
    }

    if (
      this.ir.props?.style 
      && typeof this.ir.props.style === 'object'
      && this.ir.props.style['k']
      && this.ir.props.style['k'] === 'prop'
    ) {
      const expr = resolveProps(this.ir.props.style, this.ctx);
      const apply = () => {
        const style = this.evalr.evaluate(expr, null, this.ctx);
        if(typeof style === 'object') {
          for(const property in style) {
            el.style[property] = style[property];
          }
        }
      };
      this.evalr.bindReactive(expr, apply, this.scope);
    }

    // -----------------------
    // EVENTS (exact parity with current runtime)
    // Each handler evaluates with (e, ctx)
    // Supported actions: inc, set, add, http, navigate
    // -----------------------
    for (const h of (this.ir.props?.on || [])) {
      const { event, action } = h;

      const handler = (e) => {
        preHandleEvent(e, event, action, el);
        if (!action || !action.k) return;
        // Delegate to shared runner (keeps exact semantics)
        runAction(this.evalr, this.services, this.ctx, action, e);
      };

      el.addEventListener(event, handler);
      this.listeners.push([event, handler]);
    }

    // Hybrid island boundary:
    // hydrate the container element itself, then switch descendants to CSR.
    if (isHydrating && isClientTarget) {
      this.el.textContent = '';
    }

    const childServices = (isHydrating && isClientTarget)
      ? { ...this.services, ctx: this.ctx, hydrate: { ...hydration, active: false } }
      : { ...this.services, ctx: this.ctx };

    // -----------------------
    // CHILDREN
    // Pass the same ctx to subtree (El does not augment context)
    // -----------------------
    for (const child of (this.ir.children || [])) {
      const inst = this.registry.mount(el, child, this.scope, childServices);
      this.childInstances.push(inst);
    }
  }

  update(nextIr) {
    // Optional: wire if you later support in-place updates of element tag/props.
    // For now, simplest route is to rely on reactive bindings and not swap tag.
    this.ir = nextIr;
  }

  dispose() {
    // Remove event listeners
    for (const [ev, fn] of this.listeners) this.el.removeEventListener(ev, fn);
    this.listeners.length = 0;

    // Dispose children
    for (const inst of this.childInstances) inst.dispose?.();
    this.childInstances.length = 0;
  }
}
