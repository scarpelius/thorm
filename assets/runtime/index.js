import { PrimitiveRegistry } from './core/registry.js';
import { createAtomStore } from './core/atoms.js';
import { createEvaluator } from './core/evaluator.js';
import { Scope } from './core/scope.js';
import * as nav from './core/nav.js';
import { createHttp } from './core/http.js';

import TextMount   from './primitives/TextMount.js';
import ElMount     from './primitives/ElMount.js';
import ShowMount   from './primitives/ShowMount.js';
import RepeatMount from './primitives/RepeatMount.js';
import LinkMount   from './primitives/LinkMount.js';
import RouteMount  from './primitives/RouteMount.js';
import FragmentMount  from './primitives/FragmentMount.js';
import ComponentMount from './primitives/ComponentMount.js'
import SlotMount   from './primitives/SlotMount.js';
import EffectMount from './primitives/EffectMount.js';

function createCore(ir) {
  const store = createAtomStore();
  const evalr = createEvaluator(store);
  const http  = createHttp({ atoms: evalr.atoms, evaluate: evalr.evaluate, ensureAtom: evalr.ensureAtom, notify: evalr.notify });
  const registry = new PrimitiveRegistry({
    text: TextMount,
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
  return { store, evalr, http, nav, registry };
}

export function mount(ir, container, ctx = {}) {
  const core = createCore(ir);
  const root = new Scope();
  container.textContent = '';
  const inst = core.registry.mount(container, ir.root, root, { evalr: core.evalr, nav: core.nav, http: core.http, ctx });
  return {
    dispose: () => { inst.dispose?.(); root.dispose(); },
    core: core, 
  };
}
