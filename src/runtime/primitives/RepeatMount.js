// /runtime/primitives/RepeatMount.js

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
 *              - evalr: evaluator with evaluate(), deps(), bindReactive(), subscribeMany()
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
 *   This primitive repeats its single `child` subtree for each element of `items`.
 *   It uses a user-supplied `key` expression to preserve DOM and instance identity
 *   across updates (reordering, insertions, deletions). Each row receives a
 *   per-item evaluation context `{ ...ctx, item, index }`. DOM is delimited with
 *   per-row comment anchors to allow stable range moves without wrapper elements.
 */

/**
 * @implements {Mountable}
 */
export default class RepeatMount {
  /**
   * @param {Node} parent
   * @param {Object} ir                   // shape: { k:'repeat', items: Expr, key: Expr, child: IR }
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

    // Block delimiters for the whole repeat region
    this.start = null;
    this.end   = null;

    /**
     * Map<key, Row>
     * Row = { key:any, start:Comment, end:Comment, scope:Scope, inst:Mountable }
     */
    this.keyed = new Map();
  }

  mount() {
    const hydration = this.services.hydrate;
    const cursor = hydration?.cursor;
    if (hydration?.active && cursor) {
      this.start = cursor.nextComment(this.parent, 'repeat:start');
    } else {
      this.start = document.createComment('repeat:start');
      this.end   = document.createComment('repeat:end');
      this.parent.append(this.start, this.end);
    }

    const apply = () => this._reconcile();
    // react to any deps from items or key expression
    const deps = new Set([
      ...this.evalr.deps(this.ir.items),
      ...this.evalr.deps(this.ir.key)
    ]);
    
    if (deps.size > 0) {
      apply();
      this.evalr.subscribeMany(deps, apply, this.scope);
    } else {
      apply();
    }

    if (hydration?.active && cursor) {
      this.end = cursor.nextComment(this.parent, 'repeat:end');
    }
  }

  update(nextIr) {
    // Keep reference to new IR. Reconciliation will run via reactive deps anyway.
    this.ir = nextIr;
  }

  dispose() {
    // Dispose every row’s child and scope
    for (const [, row] of this.keyed) {
      row.inst?.dispose?.();
      row.scope?.dispose?.();
    }
    this.keyed.clear();
  }

  // -----------------------
  // Reconciliation
  // -----------------------

  _reconcile() {
    const arr = this.evalr.evaluate(this.ir.items, null, this.ctx) || [];
    const next = new Map();
    const hydration = this.services.hydrate;
    const domCursor = hydration?.cursor;
    const isHydrating = !!(hydration?.active && domCursor);

    if (isHydrating) {
      for (let index = 0; index < arr.length; index++) {
        const item = arr[index];
        const rowCtx = { ...this.ctx, item, index };
        const key = this.evalr.evaluate(this.ir.key, null, rowCtx);

        const rowStart = domCursor.nextComment(this.parent, `repeat:row:${String(key)}:start`);
        const childIR =
          this.ir.tpl ??
          this.ir.child ??
          (Array.isArray(this.ir.children) ? this.ir.children[0] : undefined);
        if (!childIR) continue;

        const childScope = this.scope.fork();
        const inst = this.registry.mount(this.parent, childIR, childScope, {
          ...this.services,
          ctx: rowCtx
        });
        const rowEnd = domCursor.nextComment(this.parent, `repeat:row:${String(key)}:end`);
        next.set(key, { key, start: rowStart, end: rowEnd, scope: childScope, inst });
      }
      this.keyed = next;
      return;
    }

    // Cursor marks where to insert next row range. We insert before `insertBefore`.
    // Start with the end marker of the repeat block, and move rows in order.
    let insertBefore = this.end;

    for (let index = 0; index < arr.length; index++) {
      const item = arr[index];
      const rowCtx = { ...this.ctx, item, index };
      const key = this.evalr.evaluate(this.ir.key, null, rowCtx);

      let row = this.keyed.get(key);

      if (!row) {
        // Create a brand new row with its own range anchors and scope
        const rowStart = document.createComment(`repeat:row:${String(key)}:start`);
        const rowEnd   = document.createComment(`repeat:row:${String(key)}:end`);
        const frag = document.createDocumentFragment();
        frag.append(rowStart);

        // Accept multiple IR encodings: {child}, {tpl}, or {children[0]}
        const childIR =
          this.ir.tpl ??
          this.ir.child ??
          (Array.isArray(this.ir.children) ? this.ir.children[0] : undefined);
        if (!childIR) {
          // Nothing to mount for this row; skip gracefully.
          continue;
        }

        const childScope = this.scope.fork();
        const inst = this.registry.mount(frag, childIR, childScope, {
          ...this.services,
          ctx: rowCtx
        });

        frag.append(rowEnd);
        // Insert the whole row range before the cursor (maintains order)
        this.parent.insertBefore(frag, insertBefore);

        row = { key, start: rowStart, end: rowEnd, scope: childScope, inst };
      } else {
        // Existing row: ensure child context is updated when evaluated next time
        // (Expressions always re-evaluate with current ctx; we only need to move DOM.)
        this._moveRangeBefore(row.start, row.end, insertBefore);
      }

      // Advance cursor to just after the row range we’ve placed
      insertBefore = row.end.nextSibling || this.end;

      next.set(key, row);
    }

    // Dispose any rows that disappeared
    for (const [k, row] of this.keyed) {
      if (!next.has(k)) {
        this._removeRange(row.start, row.end);
        row.inst?.dispose?.();
        row.scope?.dispose?.();
      }
    }

    this.keyed = next;
  }

  /**
   * Moves the DOM range [start..end] so that it appears immediately before `refNode`.
   * Works by extracting the range into a fragment and inserting it.
   */
  _moveRangeBefore(startNode, endNode, refNode) {
    if (!startNode || !endNode) return;
    // If already immediately before refNode, do nothing
    if (endNode.nextSibling === refNode) return;

    const frag = document.createDocumentFragment();
    let n = startNode;
    while (n) {
      const next = n.nextSibling;
      frag.appendChild(n);
      if (n === endNode) break;
      n = next;
    }
    this.parent.insertBefore(frag, refNode);
  }

  /**
   * Removes the DOM range [start..end] (inclusive).
   */
  _removeRange(startNode, endNode) {
    let n = startNode;
    while (n) {
      const next = n.nextSibling;
      n.remove();
      if (n === endNode) break;
      n = next;
    }
  }
}
