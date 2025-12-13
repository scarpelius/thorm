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
  constructor(parent, ir, scope, { evalr, nav, http, ctx }, registry) {
    this.parent = parent;
    this.ir = ir;
    this.scope = scope;
    this.evalr = evalr;
    this.nav = nav;
    this.http = http;
    this.ctx = ctx || {};
    this.registry = registry;

    this.start = document.createComment('html:start');
    this.end = document.createComment('html:end');
  }

  mount() {
    this.parent.append(this.start, this.end);
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
