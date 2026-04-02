import PrimitiveMount from "./PrimitiveMount.js";

export default class SlotMount extends PrimitiveMount {
  constructor(parent, ir, scope, services, registry) {
    super(parent, services);
    this.parent = parent;
    this.ir = ir;
    this.scope = scope;
    this.services = services;
    this.registry = registry;

    this.name = this.ir.name ?? 'default';

    // Mounted chlld instances currently inside this slot
    this.children = [];

    // fallback comes from the slot's node's own children IR
    this.fallbackIR = Array.isArray(ir.children) ? ir.children : [];
    this.showingFallback = false;
  }

  mount() {
    // Register with parent component to receive updates
    if (
      this.services.ctx 
      && this.services.ctx.component 
      && typeof this.services.ctx.component.registerSlot === 'function'
    ) {
      this.services.ctx.component.registerSlot(this.name, this);
    };
    // If parent hasn't provided content during registration, render fallback.
    if(this.fallbackIR.length > 0 && this.children.length === 0) {
      this.renderFallback();
    }

    if (this.services?.hydrate?.active) this.finishHydrate();

    return this;
  }

  setNodes(nextIrNodes) {
    const hasNodes = Array.isArray(nextIrNodes) && nextIrNodes.length > 0;
    
    if(this.showingFallback && hasNodes) {
      this.clearChildren();
      this.showingFallback = false;
    }

    // render nodes into the slot region
    if (hasNodes) {
      this.replaceWith(nextIrNodes);
      return;
    }

    // No new nodes, render fallback or clear children
    if (this.fallbackIR.length > 0) {
      if ( !this.showingFallback ) {
        this.renderFallback();
      }
    } else {
      this.clearChildren();
    }
  }

  renderFallback() {
    this.clearChildren();
    for (const childIr of this._resolveForwardedNodes(this.fallbackIR)) {
      const inst = this.registry.mount(this.parent, childIr, this.scope, this.services);
      this.children.push(inst);
    }
    this.showingFallback = true;
  }

  clearChildren() {
    for (const inst of this.children) {
      try { inst.dispose?.(); } catch {}
    }
    this.children.length = 0;
  }

  replaceWith(nodes) {
    // replace current children (fallback or previous nodes) with new nodes
    this.clearChildren();
    for (const chidlIr of this._resolveForwardedNodes(nodes)) {
      const inst = this.registry.mount(this.parent, chidlIr, this.scope, this.services);
      this.children.push(inst);
    }
  }

  _resolveForwardedNodes(nodes) {
    const resolved = [];
    const slotSource = this.services?.ctx?.__slotForwardSource || {};

    for (const node of nodes || []) {
      if (node && typeof node === 'object' && node.k === 'slot') {
        const name = node.name ?? 'default';
        const forwarded = Array.isArray(slotSource[name]) ? slotSource[name] : [];

        if (forwarded.length > 0) {
          resolved.push(...forwarded);
          continue;
        }

        if (Array.isArray(node.children) && node.children.length > 0) {
          resolved.push(...this._resolveForwardedNodes(node.children));
          continue;
        }

        continue;
      }

      resolved.push(node);
    }

    return resolved;
  }

  dispose() {
    for (const inst of this.children) 
      inst.dispose?.();
    this.children.length = 0;
    this.start.remove;
    this.end.remove;
    this.clearChildren();
  }
}
