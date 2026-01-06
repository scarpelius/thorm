/**
 * Minimal capability invoker.
 *
 * `name` is a dotted path to a JS global/class/member, e.g.:
 *   - "Math.random"           -> Math.random()
 *   - "Date.getFullYear"      -> (new Date()).getFullYear()
 *   - "navigator.userAgent"   -> navigator.userAgent  (not callable; will error)
 *   - "navigator.clipboard.writeText" -> navigator.clipboard.writeText(...)
 *
 * This v0 intentionally avoids a huge switch() and avoids any network lookups.
 * Later you can layer an allowlist/manifest check on top.
 */

function isPlainObject(v) {
  return !!v && typeof v === 'object' && Object.getPrototypeOf(v) === Object.prototype;
}

function normalizeArgs(args) {
  // v0 policy:
  // - null/undefined => []
  // - array          => that array
  // - { args:[...] } => args
  // - otherwise      => []
  if (args == null) return [];
  if (Array.isArray(args)) return args;
  if (isPlainObject(args) && Array.isArray(args.args)) return args.args;
  return [];
}

function normalizeCtorArgs(args) {
  if (args == null) return [];
  if (isPlainObject(args) && Array.isArray(args.ctor)) return args.ctor;
  return [];
}

export async function invokeCapability(name, args) {
  const parts = String(name || '').split('.').filter(Boolean);
  if (parts.length < 2) {
    throw new Error(`Invalid capability name: ${name}`);
  }

  const rootName = parts[0];
  const root = globalThis[rootName];
  if (!root) {
    throw new Error(`Unknown capability root: ${rootName}`);
  }

  // Special rule for "Ctor.method" where method exists on prototype (e.g. Date.getFullYear)
  if (parts.length === 2 && typeof root === 'function') {
    const method = parts[1];
    if (typeof root[method] === 'function') {
      // static call (e.g. Math-like static constructors, Date.now)
      return await root[method].apply(root, normalizeArgs(args));
    }
    if (root.prototype && typeof root.prototype[method] === 'function') {
      const inst = new root(...normalizeCtorArgs(args));
      return await inst[method].apply(inst, normalizeArgs(args));
    }
    throw new Error(`Unknown capability method: ${name}`);
  }

  // Generic path resolution for objects like navigator.clipboard.writeText
  let obj = root;
  for (let i = 1; i < parts.length; i++) {
    const key = parts[i];
    const last = i === parts.length - 1;
    if (!last) {
      obj = obj?.[key];
      if (obj == null) throw new Error(`Capability path not found: ${parts.slice(0, i + 1).join('.')}`);
      continue;
    }

    const fn = obj?.[key];
    if (typeof fn !== 'function') {
      throw new Error(`Capability is not callable: ${name}`);
    }
    return await fn.apply(obj, normalizeArgs(args));
  }

  throw new Error(`Invalid capability: ${name}`);
}
