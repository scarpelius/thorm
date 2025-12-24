export default class PrimitiveMount {
  constructor(parent, services = {}) {
    this.parent = parent;
    this.services = services;
    this.dev = !!(window && window.__THORM_DEV__);

    const label = this.constructor.name.slice(0, -'Mount'.length).toLowerCase();
    const hydration = services.hydrate;
    const cursor = hydration?.cursor;

    if (hydration?.active && cursor) {
      this.start = cursor.nextComment(parent, `${label}:start`);
      this.end = null;
      this._endLabel = `${label}:end`;
      this._hydrating = true;
      return;
    }

    // create anchors
    if (this.dev) {
      this.start = document.createComment(`${label}:start`);
      this.end   = document.createComment(`${label}:end`);
    } else {
      this.start = new Text('');
      this.end   = new Text('');
    }
    parent.append(this.start, this.end);
  }

  finishHydrate() {
    if (!this._hydrating || this.end) return;
    const hydration = this.services.hydrate;
    const cursor = hydration?.cursor;
    if (!cursor) return;
    this.end = cursor.nextComment(this.parent, this._endLabel);
  }
}
