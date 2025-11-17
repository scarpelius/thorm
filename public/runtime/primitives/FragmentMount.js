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
 *   This primitive represents a logical container that does not produce a wrapper DOM node
 */

/**
 * @implements {Mountable}
 */
export default class FragmentMount {
  /**
   * @param {Node} parent
   * @param {Object} ir                   // shape: { k:'el', tag:string, props?:{attrs?:[],style?:[],cls?:Expr,on?:[]}, children?:IR[] }
   * @param {import('../core/scope.js').Scope} scope
   * @param {{ evalr:any, nav?:any, http?:any, ctx?:any }} services
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

			this.start = document.createComment('fragment:start');
			this.end = document.createComment('fragment:end');
			
			this.childInstances = [];
		}

		mount(){
			this.parent.appendChild(this.start);

			// -----------------------
			// CHILDREN
			// Pass the same ctx to subtree (El does not augment context)
			// -----------------------
			for (const child of (this.ir.children || [])) {
				const inst = this.registry.mount(this.parent, child, this.scope, {
					evalr: this.evalr,
					nav: this.nav,
					http: this.http,
					ctx: this.ctx
				});
				this.childInstances.push(inst);
			}
			this.parent.appendChild(this.end);
		}

		update(){
			this.ir = nextIr;
		}

		dispose(){
			// Dispose children
			for (const inst of this.childInstances) inst.dispose?.();
			this.childInstances.length = 0;
		}
}
