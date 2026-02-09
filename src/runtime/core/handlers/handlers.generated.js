// AUTO-GENERATED: do not edit.
// Source: /home/thorm/src/runtime/core/handlers/expr and /home/thorm/src/runtime/core/handlers/expr/op
export function concat(expr, e, ctx, helpers) { return expr.parts.map(p => helpers.evaluate(p, e, ctx)).join(''); }
export function event(expr, e, ctx, helpers) { return helpers.getByPath(e, expr.path); }
export function item(expr, e, ctx, helpers) { return helpers.getByPath(ctx && ctx.item, expr.path); }
export function not(expr, e, ctx, helpers) { return !helpers.toBool(helpers.evaluate(expr.x, e, ctx)); }
export function num(expr, e, ctx, helpers) { return Number(helpers.evaluate(expr.x, e, ctx)); }
export function param(expr, e, ctx, helpers) { return (ctx && ctx.route && ctx.route.params || {})[expr.name]; }
export function prop(expr, e, ctx, helpers) { if (!ctx || !ctx.props) return undefined; const name = expr.name; const bag = ctx.props; const hasOP = Object.prototype.hasOwnProperty.call(bag, name); if (!hasOP) return undefined; const v = bag[name]; if (v && typeof v === 'object' && v.k) { return helpers.evaluate(v, e, ctx); } if (v === undefined && expr.fallback) { return helpers.evaluate(expr.fallback, e, ctx); } return v; }
export function query(expr, e, ctx, helpers) { return (ctx && ctx.route && ctx.route.query || {})[expr.name]; }
export function read(expr, e, ctx, helpers) { const { atoms } = helpers; const a = atoms.get(expr.atom); const ret = a ? a.v : (expr.expect === 'repeat' ? [] : undefined); if (expr.expect === 'repeat') return Array.isArray(ret) ? ret : (ret ?? []); if (typeof ret === 'object') return ret; return ret; }
export function str(expr, e, ctx, helpers) { return String(helpers.evaluate(expr.x, e, ctx)); }
export function stringify(expr, e, ctx, helpers) { const space = Number(expr.space ?? 0) | 0; const v = (expr.value && typeof expr.value === 'object' && expr.k) ? helpers.evaluate(expr.value, e, ctx) : expr.value; try { return JSON.stringify(v, null, space); } catch { try { return String(v); } catch { return ''; } } }
export function val(expr) { return expr.v; }
export function op(expr, e, ctx, helpers) { const a = helpers.evaluate(expr.a, e, ctx); const b = helpers.evaluate(expr.b, e, ctx); const c = helpers.evaluate(expr.c, e, ctx); const fn = helpers.opHandlers[expr.name]; if (!fn) throw new Error('Unknown op ' + expr.name); return fn(a, b, c, { expr, e, ctx, ...helpers }); }
export function abs(a) { return Math.abs(Number(a)); }
export function add(a, b) { return Number(a) + Number(b); }
export function array_keys(a) { return (a && typeof a === 'object') ? Object.keys(a) : []; }
export function array_values(a) { return (a && typeof a === 'object') ? Object.values(a) : []; }
export function boolval(a) { return Boolean(a); }
export function ceil(a) { return Math.ceil(Number(a)); }
export function cond(a, b, c, meta) { const { expr, e, ctx, evaluate } = meta; const test = evaluate(expr.a ?? expr.a, e, ctx); return !!test ? evaluate(expr.b ?? expr.b, e, ctx) : evaluate(expr.c ?? expr.c, e, ctx); }
export function count(a) { if (Array.isArray(a)) return a.length; if (a && typeof a === 'object') return Object.keys(a).length; return 0; }
export function div(a, b) { return Number(a) / Number(b); }
export function eq(a, b) { return a === b; }
export function exp(a) { return Math.exp(Number(a)); }
export function explode(a, b) { return String(b ?? '').split(String(a ?? '')); }
export function floatval(a) { return Number.parseFloat(String(a ?? '')); }
export function floor(a) { return Math.floor(Number(a)); }
export function get(a, b, c, meta) { const { expr, e, ctx, evaluate } = meta; const obj = evaluate(expr.a, e, ctx); const key = evaluate(expr.b, e, ctx); if (obj == null) return undefined; const k = (typeof key === 'number') ? key : String(key); try { return obj[k]; } catch { return undefined; } }
export function gt(a, b) { return Number(a) > Number(b); }
export function gte(a, b) { return Number(a) >= Number(b); }
export function implode(a, b) { return Array.isArray(b) ? b.join(String(a ?? '')) : ''; }
export function in_array(a, b) { return Array.isArray(b) ? b.includes(a) : false; }
export function intval(a) { return Number.parseInt(String(a ?? ''), 10); }
export function is_array(a) { return Array.isArray(a); }
export function is_numeric(a) { return Number.isFinite(Number(a)); }
export function is_string(a) { return typeof a === 'string'; }
export function json_encode(a) { return JSON.stringify(a); }
export function log(a) { return Math.log(Number(a)); }
export function log10(a) { return Math.log10(Number(a)); }
export function log2(a) { return Math.log2(Number(a)); }
export function lt(a, b) { return Number(a) < Number(b); }
export function lte(a, b) { return Number(a) <= Number(b); }
export function ltrim(a) { return String(a ?? '').trimStart(); }
export function max(a, b) { if (b == null && Array.isArray(a)) return Math.max(...a); return Math.max(Number(a), Number(b)); }
export function min(a, b) { if (b == null && Array.isArray(a)) return Math.min(...a); return Math.min(Number(a), Number(b)); }
export function mod(a, b) { return Number(a) % Number(b); }
export function mul(a, b) { return Number(a) * Number(b); }
export function navigate(a, b, c, meta) { const { e, ctx, evaluate } = meta; const to = evaluate(action.to, e, ctx) ?? "/"; if (typeof to === 'string') { history.pushState(null, "", to); if (typeof window.__THORM_REROUTE__ === 'function') window.__THORM_REROUTE__(); } }
export function parseFloat(a) { return Number.parseFloat(String(a ?? '')); }
export function parseInt(a) { return Number.parseInt(String(a ?? ''), 10); }
export function pow(a, b) { return Math.pow(Number(a), Number(b)); }
export function round(a) { return Math.round(Number(a)); }
export function rtrim(a) { return String(a ?? '').trimEnd(); }
export function sign(a) { return Math.sign(Number(a)); }
export function sqrt(a) { return Math.sqrt(Number(a)); }
export function str_replace(a, b, c) { const search = String(a ?? ''); const replace = String(b ?? ''); const subject = String(c ?? ''); return subject.split(search).join(replace); }
export function strlen(a) { return String(a ?? '').length; }
export function strpos(a, b) { return String(a ?? '').indexOf(String(b ?? '')); }
export function strtolower(a) { return String(a ?? '').toLowerCase(); }
export function strtoupper(a) { return String(a ?? '').toUpperCase(); }
export function strval(a) { return String(a ?? ''); }
export function sub(a, b) { return Number(a) - Number(b); }
export function substr(a, b, c) { const s = String(a ?? ''); const start = Number(b ?? 0); if (c == null) return s.slice(start); const len = Number(c); return s.slice(start, start + len); }
export function trim(a) { return String(a ?? '').trim(); }
export function trunc(a) { return Math.trunc(Number(a)); }
export const opHandlers = { abs, add, array_keys, array_values, boolval, ceil, cond, count, div, eq, exp, explode, floatval, floor, get, gt, gte, implode, in_array, intval, is_array, is_numeric, is_string, json_encode, log, log10, log2, lt, lte, ltrim, max, min, mod, mul, navigate, parseFloat, parseInt, pow, round, rtrim, sign, sqrt, str_replace, strlen, strpos, strtolower, strtoupper, strval, sub, substr, trim, trunc, };
export const exprHandlers = { concat, event, item, not, num, param, prop, query, read, str, stringify, val, op, };
