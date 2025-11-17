// /runtime/core/registry.js

/**
 * Simple runtime contract verifier for class-based primitives.
 * Only active in development mode to avoid overhead in production.
 */
function assertMountable(instance, name) {
  if (!instance) {
    throw new Error(`[ThormJS] Primitive '${name}' did not return an instance.`);
  }

  const hasMount = typeof instance.mount === 'function';
  const hasDispose = typeof instance.dispose === 'function';

  if (!hasMount || !hasDispose) {
    const missing = [
      !hasMount ? 'mount()' : null,
      !hasDispose ? 'dispose()' : null
    ].filter(Boolean).join(', ');
    throw new Error(
      `[ThormJS] Primitive '${name}' must implement ${missing}.`
    );
  }

  // Optional warning if constructor forgot to store required fields
  if (!('ir' in instance) || !('scope' in instance)) {
    console.log(instance);
    console.warn(
      `[ThormJS] Primitive '${name}' does not expose expected fields (ir, scope).`
    );
  }
}

/**
 * Central dispatcher for primitive classes.
 * Responsible for instantiation, interface validation (dev),
 * and child mounting.
 */
export class PrimitiveRegistry {
  constructor(ctors = {}) {
    this.ctors = { ...ctors };
    this.opts = {};
    this.dev = !!(typeof window !== 'undefined' && window.__THORM_DEV__);
  }

  register(name, Ctor) {
    this.ctors[name] = Ctor;
  }

  configure(opts) {
    this.opts = { ...this.opts, ...opts };
  }

  /**
   * Mounts a new primitive instance.
   * @param {Node} parent
   * @param {import('./types.js').IRNode} ir
   * @param {import('./scope.js').Scope} scope
   * @param {import('./types.js').Services} services
   * @returns {import('./types.js').Mountable}
   */
  mount(parent, ir, scope, services) {
    const name = ir.k;
    const Ctor = this.ctors[name];
    if (!Ctor) {
      throw new Error(`[ThormJS] Unknown primitive '${name}'.`);
    }
    const instance = new Ctor(parent, ir, scope, services, this);
    if (this.dev) {
      try {
        assertMountable(instance, name);
      } catch (err) {
        console.error(err);
        throw err;
      }
    }
    
    instance.mount();
    return instance;
  }
}
