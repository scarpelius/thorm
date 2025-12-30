// /runtime/primitives/ShowMount.js

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
 *   This primitive conditionally renders its single child subtree when `cond` evaluates
 *   truthy. It uses comment anchors to delimit its region. The child subtree inherits
 *   the current `ctx` unchanged and is mounted within a forked scope for proper cleanup.
 */

/**
 * @implements {Mountable}
 */
export default class ShowMount {
  /**
   * @param {Node} parent
   * @param {Object} ir                   // shape: { k:'show', cond: Expr, child: IR }
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

    this.start = null;
    this.end   = null;
    this.childInstance = null;     // mounted child primitive instance
    this.childScope = null;        // forked scope for the child
  }

  mount() {
    const hydration = this.services.hydrate;
    const cursor = hydration?.cursor;
    if (hydration?.active && cursor) {
      this.start = cursor.nextComment(this.parent, 'show:start');
    } else {
      this.start = document.createComment('show:start');
      this.end = document.createComment('show:end');
      this.parent.append(this.start, this.end);
    }

    const run = () => {
      const visible = !!this.evalr.evaluate(this.ir.when, null, this.ctx);
      if (visible && !this.childInstance) {
        this.childScope = this.scope.fork();
        const useHydrate = hydration?.active && cursor;
        const mountParent = useHydrate ? this.parent : document.createDocumentFragment();
        this.childInstance = this.registry.mount(mountParent, this.ir.child, this.childScope, {
          ...this.services,
          ctx: this.ctx
        });
        if (!useHydrate) this.end.before(mountParent);
      } else if (!visible && this.childInstance) {
        // unmount child and clear DOM region
        this.childInstance.dispose?.();
        this.childInstance = null;
        this.childScope?.dispose?.();
        this.childScope = null;
        this._clearBetween();
      }
    };

    run();
    if (hydration?.active && cursor) {
      this.end = cursor.nextComment(this.parent, 'show:end');
    }
    // Re-evaluate when cond deps change; scope handles listener cleanup
    this.evalr.bindReactive(this.ir.when, run, this.scope);
  }

  update(nextIr) {
    // If you later support structural updates (e.g., changing `child`),
    // you can swap IR and re-run mount logic selectively. For now, assign:
    this.ir = nextIr;
  }

  dispose() {
    // Ensure child is cleaned up
    this.childInstance?.dispose?.();
    this.childInstance = null;
    this.childScope?.dispose?.();
    this.childScope = null;
    // No explicit DOM removal here; container removal is handled upstream.
  }

  // -----------------------
  // Private helpers
  // -----------------------
  _clearBetween() {
    let n = this.start.nextSibling;
    while (n && n !== this.end) {
      const next = n.nextSibling;
      n.remove();
      n = next;
    }
  }
}
