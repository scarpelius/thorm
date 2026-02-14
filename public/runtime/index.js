import { PrimitiveRegistry } from './core/registry.js';
import { createAtomStore } from './core/atoms.js';
import { createEvaluator } from './core/evaluator.js';
import { Scope } from './core/scope.js';
import { createCursor } from './core/hydrate.js';
import * as nav from './core/nav.js';
import { createHttp } from './core/http.js';

import TextMount   from './primitives/TextMount.js';
import HtmlMount   from './primitives/HtmlMount.js';
import ElMount     from './primitives/ElMount.js';
import ShowMount   from './primitives/ShowMount.js';
import RepeatMount from './primitives/RepeatMount.js';
import LinkMount   from './primitives/LinkMount.js';
import RouteMount  from './primitives/RouteMount.js';
import FragmentMount  from './primitives/FragmentMount.js';
import ComponentMount from './primitives/ComponentMount.js'
import SlotMount   from './primitives/SlotMount.js';
import EffectMount from './primitives/EffectMount.js';

function applyState(store, state) {
  if (!state || !state.atoms) return;
  const atoms = state.atoms;

  if (Array.isArray(atoms)) {
    for (const a of atoms) {
      if (!a || a.id == null) continue;
      store.ensureAtom(a.id);
      const v = 'value' in a ? a.value : a.initial;
      store.atoms.get(a.id).v = v;
    }
    return;
  }

  if (atoms && typeof atoms === 'object') {
    for (const [id, v] of Object.entries(atoms)) {
      const numId = Number(id);
      store.ensureAtom(numId);
      store.atoms.get(numId).v = v;
    }
  }
}

export const THORM_MODE_SSR = 'ssr';
export const THORM_MODE_CSR = 'csr';
export const THORM_TARGET_SERVER = 'server';
export const THORM_TARGET_CLIENT = 'client';

const _deprecatedWarned = {
  mount: false,
  hydrate: false,
};

function warnDeprecated(name, replacement) {
  if (typeof window === 'undefined' || !window.__THORM_DEV__) return;
  if (_deprecatedWarned[name]) return;
  _deprecatedWarned[name] = true;
  console.warn(`[ThormJS] '${name}()' is deprecated. Use ${replacement} instead.`);
}

function createCore(ir, state) {
  const store = createAtomStore();
  const evalr = createEvaluator(store);
  const http  = createHttp({ atoms: evalr.atoms, evaluate: evalr.evaluate, ensureAtom: evalr.ensureAtom, notify: evalr.notify });
  const registry = new PrimitiveRegistry({
    text: TextMount,
    html: HtmlMount,
    el: ElMount,
    show: ShowMount,
    repeat: RepeatMount,
    link: LinkMount,
    route: RouteMount,
    fragment: FragmentMount,
    component: ComponentMount,
    slot: SlotMount,
    effect: EffectMount,
  });
  store.init(ir.atoms || []);
  applyState(store, state);
  return { store, evalr, http, nav, registry };
}

export function mount(ir, container, ctx = {}) {
  warnDeprecated('mount', "boot(ir, container, { target: 'client', ctx })");
  return boot(ir, container, { target: THORM_TARGET_CLIENT, ctx });
}

export function hydrate(ir, container, state = {}, ctx = {}) {
  warnDeprecated('hydrate', "boot(ir, container, { target: 'server', state, ctx })");
  return boot(ir, container, { target: THORM_TARGET_SERVER, state, ctx });
}

export function boot(ir, container, options = {}) {
  const target = options?.target ?? ir?.root?.render?.target ?? THORM_TARGET_SERVER;
  const state = options?.state ?? null;
  const ctx = options?.ctx ?? {};
  const useHydration = target !== THORM_TARGET_CLIENT;

  window.__THORM_MODE__ = useHydration ? THORM_MODE_SSR : THORM_MODE_CSR;
  const core = createCore(ir, state);
  const root = new Scope();
  if (!useHydration) {
    container.textContent = '';
  }
  const hydration = useHydration ? { active: true, cursor: createCursor(container) } : null;
  const services = { evalr: core.evalr, nav: core.nav, http: core.http, ctx };
  if (hydration) {
    services.hydrate = hydration;
  }
  const inst = core.registry.mount(container, ir.root, root, services);
  if (hydration) {
    hydration.active = false;
  }
  return {
    dispose: () => { inst.dispose?.(); root.dispose(); },
    core: core,
  };
}
