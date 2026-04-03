import { normalizeSlotsMap, resolveProps } from "../utils/props.js";
import PrimitiveMount from "./PrimitiveMount.js";

export default class ComponentMount extends PrimitiveMount {


  constructor(parent, ir, scope, services, registry) {
    super(parent, services);
    this.parent     = parent;
    this.ir         = ir;
    this.scope      = scope;
    this.services   = services;
    this.ctx        = services.ctx || [];
    this.registry   = registry;
    this.http       = services.http;

    this.propsVal   = [];
    this.slots      = [];
    this.propStops  = [];
    this.propExprs  = [];
    this.childScope = null;
    this.childCtx   = null;
    this.childInst  = null;
    this.slotsVal   = normalizeSlotsMap(ir.slots || []);
  }

  mount() {
    this.childScope = this.scope.fork?.() ?? this.scope;

    // Seed initial prop values before mounting the child tree so first render
    // sees the same prop shape as SSR.
    this.bindProps(this.ir.props || {});

    const childCtx = Object.assign({}, this.ctx, {
      props: this.propsVal,
      slots: this.slots,   // slot placeholders will register here
      component: this,     // allow SlotMount to register with this component
      __slotSource: this.slotsVal,
      __slotForwardSource: this.ctx?.__slotSource ?? this.ctx?.__slotForwardSource ?? this.slotsVal,
      __propsExpr: this.resolvePropsMap(this.ir.props || {}),
    });
    this.childCtx = childCtx;
    
    const hydration = this.services.hydrate;
    const cursor = hydration?.cursor;
    const useHydrate = hydration?.active && cursor;
    const mountParent = useHydrate ? this.parent : document.createDocumentFragment();
    this.childInst = this.registry.mount(
      mountParent,
      this.ir.tpl,
      this.childScope,
      { ...this.services, http: this.http, ctx: childCtx }
    );
    if (!useHydrate) this.parent.insertBefore(mountParent, this.end);
    if (useHydrate) this.finishHydrate();
    //this.seedSlots();
    return this;
  }

  //seedSlots() {
  //  console.log(this.slots);
  //  for (const [name, nodes] of Object.entries(this.slotsVal)) {
  //    const slot = this.slots[name];
  //    if(slot) {
  //      slot.setNodes(nodes);
  //    }
  //  }
  //}

  update(nextNode) {
    this.bindProps(nextNode.props) || [];
    this.reconcileSlots(nextNode.props || []);
    this.ir = nextNode;
    if (this.childCtx) {
      this.childCtx.__propsExpr = this.resolvePropsMap(nextNode.props || {});
    }
  }

  // Called by SlotMount to attach an instance for a given name
  registerSlot(name, slotInst){
    const key = name || 'default';
    this.slots[key] = slotInst;
    const nodes = this.slotsVal[key] || [];
    if (nodes.length) slotInst.setNodes(nodes);
  }

  bindProps(nextProps) {
    const toKeep = new Set(Object.keys(nextProps || []));

    // Unsubscribe props that disappeared
    for(const name of Object.keys(this.propStops)) {
      if( !toKeep.has(name) ) {
        try { this.propStops[name](); } catch {}
        delete this.propStops[name];
        delete this.propExprs[name];
        delete this.propsVal[name];
        this.queuePropsChanged();
      }
    }

    // (re)subscribe current props, rebinding if the *Expr object* changed
    for(const [name, expr] of Object.entries(nextProps || [])) {
      const prevExpr = this.propExprs[name];
      const needsRebind = !prevExpr || prevExpr !== expr;
      if(needsRebind) {
        const resolvedExpr = resolveProps(expr, this.ctx);
        // dispose old
        if(this.propStops[name]) {
          try { this.propStops[name](); } catch {}
        }
        // bind new
        const apply = () => {
          const v = this.services.evalr.evaluate(resolvedExpr, null, this.ctx);
          this.propsVal[name] = v;
          this.queuePropsChanged();
        };
        this.propStops[name] = this.services.evalr.bindReactive(resolvedExpr, apply, this.childScope);
        this.propExprs[name] = expr;

        // Run once asynchronously so child sees initial props immediately
        try {
          this.propsVal[name] = this.services.evalr.evaluate(resolvedExpr, null, this.ctx);
          this.queuePropsChanged();
        } catch {}
      }
    }
  }

  resolvePropsMap(props) {
    const out = {};
    for (const [name, expr] of Object.entries(props || {})) {
      out[name] = resolveProps(expr, this.ctx);
    }
    return out;
  }

  queuePropsChanged() {
    if ( this._propsChanged ) return;
    this._propsChanged = true;
    queueMicrotask(() => {
      this._propsChanged = false;
      // If childInst exposes updateProps hook, call it; otherwise readers will re-evaluate from ctx.props
      if(this.childInst && typeof this.childInst.updateProps === 'function') {
        this.childInst.updateProps(this.propsVal);
      }
    });
  }

  reconcileSlots(nextSlots) {
    const next = normalizeSlotsMap(nextSlots || []);

    // Add/Update
    for(const [name, nodes] of Object.entries[next]) {
      const slot = this.slots[name];
      if(slot) slot.setNodes(nodes);
    }

    // Clear/Remove
    for(const name of Object.keys(this.slotsVal)) {
      if (!(name in next)) {
        const slot = this.slots[name];
        if (slot) slot.setNodes([]);
      }
    }
    this.slotsVal = next;
    this.slotVal = next;
    if (this.childCtx) {
      this.childCtx.__slotSource = next;
      this.childCtx.__slotForwardSource = this.ctx?.__slotSource ?? this.ctx?.__slotForwardSource ?? next;
    }
  }

  dispose(){
    for(const k in this.propStops) {
      try { this.propStops[k](); } catch {}
    } 

    this.propStops = {};
    this.propExprs = {};
    if (this.childInst) this.childInst.dispose?.();
    if (this.childScope && this.childInst.scope !== this.scope) {
      this.childScope.dispose?.();
    }
    if (this.start) this.start.remove();
    if (this.end) this.end.remove();
  }
}
