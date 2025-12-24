// /runtime/primitives/HtmlMount.js

/**
 * @implements {Mountable}
 * Renders raw HTML markup delimited by comment anchors so surrounding layout
 * remains intact. Markup is re-evaluated reactively when its expression changes.
 */
import { resolveProps } from './../utils/props.js';

export default class HtmlMount {
  /**
   * @param {Node} parent
   * @param {Object} ir                   // shape: { k:'html', value: Expr }
   * @param {import('../core/scope.js').Scope} scope
   * @param {{ evalr: any, nav?: any, http?: any, ctx?: any }} services
   * @param {import('../core/registry.js').PrimitiveRegistry} registry
   */
  constructor(parent, ir, scope, services, registry) {
    this.parent = parent;
    this.ir = ir;
    this.scope = scope;
    this.evalr = services.evalr;
    this.nav = services.nav;
    this.http = services.http;
    this.ctx = services.ctx || {};
    this.services = services;
    this.registry = registry;

    this.start = null;
    this.end = null;
  }

  mount() {
    const hydration = this.services.hydrate;
    const cursor = hydration?.cursor;
    if (hydration?.active && cursor) {
      this.start = cursor.nextComment(this.parent, 'html:start');
      // Consume and skip all nodes until the closing anchor
      let n = null;
      do {
        n = cursor.nextNode(this.parent);
        if (!n) {
          throw new Error('[hydrate] expected comment html:end but no node found');
        }
      } while (n.nodeType !== 8 || n.nodeValue !== 'html:end');
      this.end = n;
    } else {
      this.start = document.createComment('html:start');
      this.end = document.createComment('html:end');
      this.parent.append(this.start, this.end);
    }
    const expr = resolveProps(this.ir.value, this.ctx);

    const apply = () => {
      const html = this.evalr.evaluate(expr, null, this.ctx);
      this._clearBetween();
      const markup = html == null ? '' : String(html);
      if (!markup) return;
      const tpl = document.createElement('template');
      tpl.innerHTML = markup;
      this.end.before(tpl.content);
    };

    this.evalr.bindReactive(expr, apply, this.scope);
  }

  update(nextIr) {
    this.ir = nextIr;
  }

  dispose() {
    this._clearBetween();
  }

  _clearBetween() {
    let n = this.start.nextSibling;
    while (n && n !== this.end) {
      const next = n.nextSibling;
      n.remove();
      n = next;
    }
  }
}
