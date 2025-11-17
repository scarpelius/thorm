export function createAtomStore() {
  const atoms = new Map();    // id -> { v }
  const subs  = new Map();    // id -> Set<fn>
  let scheduled = false;
  const queue = new Set();

  function init(initialAtoms = []) {
    atoms.clear(); 
    subs.clear();
    for (const a of initialAtoms) {
      atoms.set(a.id, { v: a.initial });
      subs.set(a.id, new Set());
    }
  }

  function ensureAtom(id, init) {
    if (!atoms.has(id))
      atoms.set(id, { v: init });
    if (!subs.has(id))
      subs.set(id, new Set());
  }

  function addSub(id, fn) {
    ensureAtom(id);
    subs.get(id).add(fn); 
  }

  function removeSub(id, fn) {
    subs.get(id)?.delete(fn); 
  }

  function notify(id) {
    queue.add(id); 
    if (!scheduled) { 
      scheduled = true; 
      queueMicrotask(flush);
    }
  }

  function flush() {
    scheduled = false;
    const runs = new Set();
    for (const id of queue) 
      for (const fn of (subs.get(id) || [])) 
        runs.add(fn);
    queue.clear();
    for (const fn of runs) {
      try {
        fn();
      } catch (e) {
        console.error(e);
      } 
    }
  }

  /*function subscribeMany(depSet, fn, scope) {
    for (const id of depSet)
      addSub(id, fn);
    scope?.onDispose?.(() => { 
      for (const id of depSet)
        removeSub(id, fn); 
    });
  }*/

  function subscribeMany(depSet, fn, scope) {
    const ids = Array.from(depSet);
    for (const id of ids) 
      addSub(id, fn);

    let disposed = false;
    const stop = () => {
      if (disposed) return;
      disposed = true;
      for (const id of ids) removeSub(id, fn);
    };

    // also clean up when the owning scope dies
    scope?.onDispose?.(stop);
    return stop;
  }
  
  return { atoms, subs, init, ensureAtom, addSub, removeSub, notify, flush, subscribeMany };
}
