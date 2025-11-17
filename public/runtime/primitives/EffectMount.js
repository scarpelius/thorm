import { runActions } from '../core/actions.js';

export default class EffectMount {
  constructor(parent, ir, scope, services, registry) {
    // Registry calls all mounts as (ir, scope)
    this.ir         = ir;
    this.scope      = scope;
    this.parent     = parent || null;
    this.evalr      = services.evalr;
    this.nav        = services.nav;
    this.http       = services.http;
    this.ctx        = services.ctx || {};
    this.services   = services || null;
    this.start      = null;
    this.end        = null;
    this.disposers  = [];
  }

  mount(start, end) {
    this.start = start; this.end = end;

    //const defaultSelf = this._resolveTargetElement();

    for (const trig of this.ir.triggers || []) {
      switch (trig.type) {
        case 'mount': {
          queueMicrotask(() => runActions(this.evalr, this.services, this.ctx, this.ir.actions, null));
          break;
        }
        case 'watch': {
          this._setupWatch(trig);
          break;
        }
        case 'interval': {
          const id = setInterval(() => {
            runActions(this.evalr, this.scope, this.ctx, this.ir.actions, null);
          }, Math.max(1, Number(trig.ms || 0)));
          this.disposers.push(() => clearInterval(id));
          break;
        }
        case 'timeout': {
          const id = setTimeout(() => {
            runActions(this.evalr, this.scope, this.ctx, this.ir.actions, null);
          }, Math.max(1, Number(trig.ms || 0)));
          this.disposers.push(() => clearTimeout(id));
          break;
        }
        case 'visible': {
          const el = this._resolveTargetElement();
          if (!el || typeof IntersectionObserver === 'undefined') {
            if (this.scope.dev) console.warn('[effect] visible: missing target or IO unsupported');
            break;
          }
          const opts = {};
          if (typeof trig.threshold === 'number') opts.threshold = trig.threshold;
          if (typeof trig.rootMargin === 'string') opts.rootMargin = trig.rootMargin;
          const triggerWhen = trig.when || 'enter';
          const io = new IntersectionObserver((entries) => {
            for (const e of entries) {
              const isIn = !!e.isIntersecting;
              if ((triggerWhen === 'enter' && isIn) ||
                  (triggerWhen === 'exit'  && !isIn) ||
                  (triggerWhen === 'both')) {
                runActions(this.evalr, this.services, this.ctx, this.ir.actions, null);
              }
            }
          }, opts);
          io.observe(el);
          this.disposers.push(() => io.disconnect());
          break;
        }
        case 'event': {
          const target = this._resolveEventTarget(trig.on || 'self');
          const opts = trig.options || {};
          
          if (!target?.addEventListener) {
            if (this.scope.dev) console.warn('[effect] event: target not found for', trig.on);
            break;
          }
          
          const handler = (evt) => {
            if (opts.preventDefault) evt.preventDefault?.();
            if (opts.stopPropagation) evt.stopPropagation?.();
            runActions(this.evalr, this.services, this.ctx, this.ir.actions, evt);
          };
          target.addEventListener(trig.event, handler, opts);
          this.disposers.push(() => target.removeEventListener(trig.event, handler, opts));
          break;
        }
        default: {
          if (this.scope.dev) console.warn('[effect] unknown trigger type', trig);
        }
      }
    }

    return { start: this.start, end: this.end };
  }

  dispose() {
    for (const d of this.disposers) try { d(); } catch {}
    this.disposers.length = 0;
  }

  /* ---------- helpers ---------- */

  _resolveEventTarget(on, selfEl) {
    if (on === 'window') return window;
    if (on === 'document') return document;
    const t = this.ir.target;
    if (t?.type === 'window')   return window;
    if (t?.type === 'document') return document;
    if (t?.type === 'selector' && typeof t.selector === 'string') {
      const el = document.querySelector(t.selector);
      if (el) return el;
    }
    // otherwise fall back to nearest element ancestor
    return this._nearestElementAncestor();
    return selfEl;
  }

  _nearestElementAncestor() {
    let p = this.parent;
    while (p) {
      if (p.node instanceof Element) return p.node;
      if (p.start && p.start.parentNode instanceof Element) return p.start.parentNode;
      p = p.parent || null;
    }
    return null;
  }

  _resolveTargetElement() {
    const t = this.ir.target;
    if (t?.type === 'selector' && typeof t.selector === 'string') {
      const el = document.querySelector(t.selector);
      if (el) return el;
    }
    // walk up to nearest element-producing parent
    let p = this.parent;
    while (p) {
      if (p.node instanceof Element) return p.node;
      if (p.start && p.start.parentNode instanceof Element) return p.start.parentNode;
      p = p.parent || null;
    }
    return null;
  }

  _setupWatch(trig) {
    const apply = () => runActions(this.evalr, this.scope, this.ctx, this.ir.actions, null);
    const wrapped = this._wrapDebounceThrottle(apply, trig.debounceMs ?? null, trig.throttleMs ?? null);

    if (typeof this.evalr?.bindReactive === 'function') {
      const unbind = this.evalr.bindReactive(trig.expr, wrapped, this);
      this.disposers.push(() => { try { unbind(); } catch {} });
      if (trig.immediate) wrapped();
      return;
    }
    if (typeof this.scope.atoms?.subscribe === 'function') {
      const unsub = this.scope.atoms.subscribe(() => wrapped());
      this.disposers.push(() => { try { unsub(); } catch {} });
      if (trig.immediate) wrapped();
      return;
    }
    if (this.scope.dev) console.warn('[effect] watch: no reactive binding API (bindReactive/atoms.subscribe)');
    if (trig.immediate) wrapped();
  }

  _wrapDebounceThrottle(fn, debounceMs, throttleMs) {
    let last = 0, timer = null;
    const fire = () => { last = performance.now(); fn(); };
    const throttled = () => {
      const t = throttleMs !== null && typeof throttleMs === 'object'
          ? Number(this.evalr.evaluate(throttleMs, null, this.ctx) || 0)
          : throttleMs;
      if (typeof t === 'number' && t > 0) {
        if (performance.now() - last < t) return;
      }
      fire();
    };
    if (typeof debounceMs === 'number' && debounceMs > 0) {
      return () => { 
        if (timer) clearTimeout(timer); 
        timer = setTimeout(() => { timer = null; throttled(); }, debounceMs); 
      };
    }

    if (debounceMs !== null && typeof debounceMs === 'object') {
      return () => { 
        const d = Number(this.evalr.evaluate(debounceMs, null, this.ctx) || 0);
        if (timer) clearTimeout(timer); 
        timer = setTimeout(() => { timer = null; throttled(); }, d); 
      };
    }

    return throttled;
  }
}
