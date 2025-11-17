//const runtime = await getRuntime();
const runtime = window.__THORM_RUNTIME__;

// Devtools runs only when window.__THORM_DEV__ is truthy.
if (!window.__THORM_DEV__) { /* noop in prod */ }
else (
  async function () {
    
    let style=null;
    // --- tiny utilities
    const $ = (sel, el=document) => el.querySelector(sel);
    const el = (tag, cls, html) => { const n=document.createElement(tag); if(cls) n.className=cls; if(html!=null) n.innerHTML=html; return n; };
    const esc = s => String(s).replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));
    const now = () => performance?.now?.() ?? Date.now();

    // --- try to discover runtime singletons without importing/patching modules
    function findRuntime() {
      // 0) Fast-path hints (optional; not required)
      //if (window.__THORM_RUNTIME__) {
        if (runtime.atoms instanceof Map) {
          return { 
            host: runtime, 
            atoms: runtime.atoms, 
            subs: runtime.subs instanceof Map ? runtime.subs : new Map(), 
            notify: typeof runtime.notify === 'function' ? runtime.notify : null 
          };
        }
      //}
      /*
      // 1) Obvious top-level candidates
      const seeds = [window.app, window.runtime, window.PJP, window.PHPJS, window.Thorm, window.thorm, window.services, window];
      const seen = new Set();
      const q = [];

      for (const s of seeds) if (s && typeof s === 'object') { q.push(s); seen.add(s); }

      // 2) BFS with tight budget to avoid freezing the page
      const MAX_NODES = 800;    // hard cap for examined objects
      const MAX_PROPS = 60;     // per-object
      const candidates = [];    // { host, atoms, subs, notify, score }

      let inspected = 0;
      while (q.length && inspected < MAX_NODES) {
        const obj = q.shift(); inspected++;

        // Collect obvious shapes on this object
        const maybeMaps = [];
        try {
          // Direct props that look like registries
          for (const k of Object.keys(obj).slice(0, MAX_PROPS)) {
            const v = obj[k];
            if (v instanceof Map) maybeMaps.push({ key: k, map: v, host: obj });
            if (v && typeof v === 'object' && !seen.has(v)) { seen.add(v); q.push(v); }
          }
        } catch { /* ignore cross-origin / getters // }

        for (const { key, map, host } of maybeMaps) {
          const score = scoreAtomsMap(map);
          if (score > 0) {
            const subs = host.subs instanceof Map ? host.subs : (host.deps instanceof Map ? host.deps : new Map());
            const notify = typeof host.notify === 'function' ? host.notify
                        : typeof host.emit === 'function' ? host.emit
                        : null;
            candidates.push({ host, atoms: map, subs, notify, score, hint: key });
          }
        }
      }

      // 3) Pick the best-scoring candidate
      candidates.sort((a,b) => b.score - a.score);
      const best = candidates[0];
      if (best) return { host: best.host, atoms: best.atoms, subs: best.subs, notify: best.notify };

      // 4) Fallback: empty facade
      return { host: null, atoms: new Map(), subs: new Map(), notify: null };

      // Heuristic: numeric keys, small range, object values with {v}|{value}|{current}
      function scoreAtomsMap(map) {
        if (!(map instanceof Map)) return 0;
        let n = 0, numericKeys = 0, hasShape = 0, maxKey = 0, objVals = 0;
        for (const [k, v] of map) {
          n++;
          const kn = typeof k === 'number' ? k : Number.isInteger(+k) ? +k : NaN;
          if (!Number.isNaN(kn)) { numericKeys++; if (kn > maxKey) maxKey = kn; }
          if (v && typeof v === 'object') {
            objVals++;
            if ('v' in v || 'value' in v || 'current' in v) hasShape++;
          }
          if (n > 100) break; // sample first 100
        }
        if (n === 0) return 0;
        // scoring: prefer numeric keys, consecutive-ish, and value objects with 'v' etc.
        let s = 0;
        s += (numericKeys / n) * 5;
        s += (objVals / n) * 2;
        s += (hasShape / n) * 8;
        // tiny bonus if keys look like 1..N
        if (numericKeys && maxKey && maxKey <= n * 2) s += 2;
        return s;
      }

      */
    }

    let RT = findRuntime();
    if (!RT.atoms) {
      // last resort: create a read-only facade that never crashes
      RT = { host:null, atoms:new Map(), subs:new Map(), notify:null };
    }

    // --- non-invasive change feed: wrap host.notify if it exists and is mutable, else poll
    const listeners = new Set();
    function emit(ev){ for (const fn of listeners) try{ fn(ev); }catch{} }

    let unhook = null;
    if (RT.host && RT.notify && Object.isExtensible(RT.host)) {
      const original = RT.notify.bind(RT.host);
      RT.host.notify = function (id, ...rest) {
        const t0 = now();
        const ret = original(id, ...rest);
        queueMicrotask(() => {
          const value = RT.atoms.get(id)?.v;
          emit({ type:'atom-change', id, value, ts: now(), dt: now()-t0 });
        });
        return ret;
      };
      unhook = () => { try { RT.host.notify = original; } catch {} };
    } else {
      // polling fallback (no runtime mutation at all)
      let last = snapshotAtoms(RT.atoms);
      const iv = setInterval(() => {
        const cur = snapshotAtoms(RT.atoms);
        for (const [id, v] of cur) {
          if (!last.has(id) || !deepEq(last.get(id), v)) {
            emit({ type:'atom-change', id, value: v, ts: now(), dt: 0 });
          }
        }
        last = cur;
      }, 150);
      unhook = () => clearInterval(iv);
    }

    function snapshotAtoms(map) {
      const out = new Map();
      if (!(map instanceof Map)) return out;
      for (const [id, rec] of map.entries()) out.set(id, safeClone(rec?.v));
      return out;
    }
    function safeClone(v) { try { return JSON.parse(JSON.stringify(v)); } catch { return v; } }
    function deepEq(a,b){ try { return JSON.stringify(a)===JSON.stringify(b);} catch { return a===b; } }

    // --- public-ish API used by panel
    const DevAPI = {
      getAtoms() {
        const rows = [];
        if (RT.atoms instanceof Map) {
          for (const [id, rec] of RT.atoms.entries()) {
            rows.push({ id, value: rec?.v, subscribers: RT.subs.get(id)?.size ?? 0 });
          }
        } else {
          console.warn('Devtoos: failed to read atoms');
        }
        return rows.sort((a,b)=>a.id-b.id);
      },
      setAtom(id, value) {
        if (!(RT.atoms instanceof Map) || !RT.atoms.has(id)) return false;
        const rec = RT.atoms.get(id);
        if (!rec) return false;
        rec.v = value;
        if (RT.notify) { try { RT.notify(id); } catch {} }
        return true;
      },
      onEvent(fn) { listeners.add(fn); return () => listeners.delete(fn); },
      highlight(id, ms=1000) {
        // Best-effort: briefly flash the most recently mutated text/element nodes
        // We don’t have binding ↔ atom graph without runtime hooks, so we use a short MutationObserver window.
        const hi = (node) => {
          const el = node?.nodeType === 1 ? node : node?.parentElement;
          if (!el) return;
          el.classList.add('pjp-devtools-highlight');
          setTimeout(()=>el.classList.remove('pjp-devtools-highlight'), ms);
        };
        // Flash all text nodes in view that changed recently (heuristic)
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null);
        let n=0, t=0;
        while (walker.nextNode()) {
          const tn = walker.currentNode;
          if (tn.nodeValue && tn.nodeValue.trim().length) { hi(tn); n++; if(++t>200) break; }
        }
        return n;
      },
      destroy(){ unhook?.(); panel?.remove(); style?.remove(); }
    };

    // --- UI (overlay) — no dependencies
    injectCss(`
  .pjp-devtools{position:fixed;right:12px;bottom:12px;width:420px;max-height:70vh;background:#111;color:#ddd;border:1px solid #333;border-radius:12px;font:12px/1.4 ui-monospace,monospace;display:flex;flex-direction:column;overflow:hidden;z-index:99999}
  .pjp-bar{display:flex;align-items:center;justify-content:space-between;padding:6px 10px;background:#1b1b1b;border-bottom:1px solid #222}
  .pjp-body{display:grid;grid-template-columns: 1fr;gap:8px;padding:8px}
  .pjp-row{display:grid;grid-template-columns: 44px 1fr 72px 42px 48px;gap:6px;align-items:center;margin-bottom:6px}
  .pjp-input{width:100%;background:#000;color:#fff;border:1px solid #333;padding:3px 6px}
  .pjp-log{margin-top:8px;max-height:30vh;overflow:auto;background:#0b0b0b;border:1px solid #222;padding:6px}
  .pjp-logline{padding:2px 0;border-bottom:1px dotted #222}
  .pjp-btn{background:#222;color:#ddd;border:1px solid #333;padding:2px 6px;border-radius:6px;cursor:pointer}
  .pjp-btn:hover{background:#2b2b2b}
  .pjp-devtools-highlight{outline:2px solid #8ab4f8!important;outline-offset:2px;transition:outline-color .2s}
    `);

    const panel = el('div','pjp-devtools',`
      <div class="pjp-bar"><strong>Atom Inspector</strong><div><button class="pjp-btn" data-refresh>Refresh</button> <button class="pjp-btn" data-close>×</button></div></div>
      <div class="pjp-body">
        <div data-list></div>
        <div class="pjp-log" data-log></div>
      </div>
    `);
    document.body.appendChild(panel);
    $('[data-close]', panel).onclick = ()=>DevAPI.destroy();
    $('[data-refresh]', panel).onclick = ()=>renderList();

    const list = $('[data-list]', panel);
    const log  = $('[data-log]', panel);

    function renderList() {
      const rows = DevAPI.getAtoms().map(a=>`
        <div class="pjp-row">
          <span>#${a.id}</span>
          <input class="pjp-input" data-id="${a.id}" value="${esc(JSON.stringify(a.value))}"/>
          <span>${a.subscribers} deps</span>
          <button class="pjp-btn" data-hl="${a.id}">🔦</button>
          <button class="pjp-btn" data-set="${a.id}">Set</button>
        </div>`).join('');
      list.innerHTML = rows || '<em>No atoms found.</em>';
      list.querySelectorAll('[data-set]').forEach(btn=>{
        btn.onclick = () => {
          const id = Number(btn.getAttribute('data-set'));
          const box = list.querySelector(`input[data-id="${id}"]`);
          let val = box.value;
          try { val = JSON.parse(val); } catch {}
          DevAPI.setAtom(id, val);
        };
      });
      list.querySelectorAll('[data-hl]').forEach(btn=>{
        btn.onclick = () => DevAPI.highlight(Number(btn.getAttribute('data-hl')));
      });
    }

    const off = DevAPI.onEvent(ev=>{
      if (ev.type==='atom-change') {
        const line = el('div','pjp-logline',`#${ev.id} → ${esc(JSON.stringify(ev.value))} @ ${ev.ts.toFixed(1)}ms`);
        log.prepend(line);
        renderList();
      }
    });

    // initial render
    renderList();

    // --- helpers
    
    function injectCss(css){
      style = document.createElement('style');
      style.textContent = css;
      document.head.appendChild(style);
    }

    // expose for debugging if needed (dev only)
    Object.defineProperty(window,'THORM_DEVTOOLS',{ value:{ DevAPI, RT }, enumerable:false });
})();
