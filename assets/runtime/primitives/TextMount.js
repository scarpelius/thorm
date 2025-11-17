// /runtime/primitives/TextMount.js

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
 *   dispose() → called before removal; must release any resources
 *
 * Primitive description:
 *   This primitive renders a text node whose content is driven by an expression.
 *   The expression is evaluated with access to the current subtree `ctx`
 *   (e.g., repeat item, route params). It re-renders reactively when its deps change.
 */

import { resolveProps } from './../utils/props.js';

/**
 * @implements {Mountable}
 */
export default class TextMount {
  /**
   * @param {Node} parent
   * @param {Object} ir                   // shape: { k:'text', value: Expr }
   * @param {import('../core/scope.js').Scope} scope
   * @param {{ evalr: any, nav?: any, http?: any, ctx?: any }} services
   * @param {import('../core/registry.js').PrimitiveRegistry} registry
   */
  constructor(parent, ir, scope, { evalr, nav, http, ctx }, registry) {
    this.parent = parent;
    this.ir = ir;
    this.scope = scope;
    this.evalr = evalr;
    this.nav = nav;
    this.http = http;
    this.ctx = ctx || {};
    this.registry = registry;

    this.node = document.createTextNode('');
  }

  mount() {
    this.parent.appendChild(this.node);
    const expr = resolveProps(this.ir.value, this.ctx);

    const apply = () => {
      // Evaluate the bound expression with subtree ctx
      const v = this.evalr.evaluate(expr, null, this.ctx);
      this.node.nodeValue = v == null ? '' : String(v);
    };

    // Re-run whenever deps of ir.value change; scope handles cleanup
    this.evalr.bindReactive(expr, apply, this.scope);
  }

  // Not used today, but here for forward-compat if IR diffing lands later.
  update(nextIr) {
    this.ir = nextIr;
    // A no-op here because bindReactive re-evaluates on dep changes.
    // If you later support structural changes, re-bind here accordingly.
  }

  dispose() {
    // Nothing to clean up: text node has no listeners;
    // reactive bindings were attached with scope and will be disposed upstream.
  }
  
}
