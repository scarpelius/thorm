// /runtime/core/types.js

/**
 * ThormJS Runtime Type Definitions
 * --------------------------------
 * Pure-JS JSDoc typedefs for editor intelligence and light type checking.
 *
 * To enable type checking & autocomplete in VS Code:
 *   1) Add jsconfig.json at project root:
 *      {
 *        "compilerOptions": { "checkJs": true, "target": "ES2022", "module": "ESNext" },
 *        "include": ["runtime"]
 *      }
 *
 * NOTE: This file has no runtime effect. It exists for DX only.
 */

/** ===== Core classes & factory return types ===== */

/**
 * @typedef {import('./scope.js').Scope} Scope
 */

/**
 * Evaluator service (created by createEvaluator()).
 * Provides expression evaluation, dependency discovery, and reactive binding.
 * You can expand this with more precise Expr typing later.
 * @typedef {Object} Evaluator
 * @property {(expr: any, e?: Event|null, ctx?: any) => any} evaluate
 * @property {(expr: any, out?: Set<string>) => Set<string>} deps
 * @property {(expr: any, apply: () => void, scope: Scope) => void} bindReactive
 * @property {Map<string, {v:any}>} atoms
 * @property {(id: string) => void} notify
 * @property {(ids: Set<string>, fn: () => void, scope: Scope) => void} subscribeMany
 */

/**
 * Primitive registry (dispatcher).
 * @typedef {Object} PrimitiveRegistry
 * @property {(name: string, Ctor: new (...args:any[]) => Mountable) => void} register
 * @property {(opts: Object) => void} configure
 * @property {(parent: Node, ir: IRNode, scope: Scope, services: Services) => Mountable} mount
 */

/** ===== Services bundle passed to every primitive ===== */

/**
 * Services provided to every mountable primitive by the runtime.
 * @typedef {Object} Services
 * @property {Evaluator} evalr                             // evaluate(), deps(), bindReactive(), atoms, notify, subscribeMany
 * @property {Object} [nav]                                // optional navigation API (navigate(), currentPath(), parseQuery()) if you export one
 * @property {Object} [http]                               // optional HTTP API (performHttp()) if you export one
 * @property {any} [ctx]                                   // per-subtree evaluation context (e.g., { item }, { params, query })
 */

/** ===== IR node shapes ===== */

/**
 * Common discriminant + fallback index signature.
 * Extend per node kind as below; keep `k` as the tag.
 * @typedef {Object} IRNodeBase
 * @property {string} k
 */

/** Text */
/// { k:'text', value: Expr }
/// For now Expr is typed as `any`. Tighten later if desired.
/**
 * @typedef {IRNodeBase & {
 *   k: 'text',
 *   value: any
 * }} TextIR
 */

/** El (element) */
/// { k:'el', tag:string, props?:{ attrs?: [any, string, any][], style?: [any, string, any][], cls?: any, on?: {event:string, action:any}[] }, children?: IRNode[] }
/**
 * @typedef {IRNodeBase & {
 *   k: 'el',
 *   tag: string,
 *   props?: {
 *     attrs?: Array<[any, string, any]>,
 *     style?: Array<[any, string, any]>,
 *     cls?: any,
 *     on?: Array<{ event: string, action: any }>
 *   },
 *   children?: IRNode[]
 * }} ElIR
 */

/** Show */
/// { k:'show', cond:any, child: IRNode }
/**
 * @typedef {IRNodeBase & {
 *   k: 'show',
 *   cond: any,
 *   child: IRNode
 * }} ShowIR
 */

/** Repeat */
/// { k:'repeat', items:any, key:any, child: IRNode }
/**
 * @typedef {IRNodeBase & {
 *   k: 'repeat',
 *   items: any,
 *   key: any,
 *   child: IRNode
 * }} RepeatIR
 */

/** Link */
/// { k:'link', to:any, replace?: boolean|any, cls?: any, attrs?: Array<[any, string, any]>, children?: IRNode[] }
/**
 * @typedef {IRNodeBase & {
 *   k: 'link',
 *   to: any,
 *   replace?: boolean|any,
 *   cls?: any,
 *   attrs?: Array<[any, string, any]>,
 *   children?: IRNode[]
 * }} LinkIR
 */

/** Route */
/// { k:'route', base?: string, table: Array<{re:string|RegExp, keys?: string[]}>, views: IRNode[], fallback: IRNode }
/**
 * @typedef {IRNodeBase & {
 *   k: 'route',
 *   base?: string,
 *   table: Array<{ re: string|RegExp, keys?: string[] }>,
 *   views: IRNode[],
 *   fallback: IRNode
 * }} RouteIR
 */

/**
 * Union of all IR nodes supported by the runtime.
 * Add new kinds here when you add new primitives (e.g., FragmentIR, ComponentIR).
 * @typedef {TextIR | ElIR | ShowIR | RepeatIR | LinkIR | RouteIR} IRNode
 */

/**
 * @typedef {Object} SlotIR
 * @property {'slot'} k
 * @property {string} [name]
 * @property {IRNode[]} [fallback]
 *
 * @typedef {Object} ComponentIR
 * @property {'component'} k
 * @property {IRNode} tpl
 * @property {Object.<string, Expr>} [props]
 * @property {Object.<string, IRNode[]>} [slots]   // includes 'default' if set
 * @property {Expr} [key]
 */


/** ===== Mountable interface (implemented by class-based primitives) ===== */

/**
 * The common contract that all class-based primitives must implement.
 * (Informal interface; enforced by docs + optional dev-time checks.)
 *
 * @interface Mountable
 * @param {Node} parent
 * @param {IRNode} ir
 * @param {Scope} scope
 * @param {Services} services
 * @param {PrimitiveRegistry} registry
 * @method mount
 * @method [update]
 * @method dispose
 */
