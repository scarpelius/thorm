const styles = `
  :root {
    color-scheme: light;
    --bg: #f6f3ea;
    --panel: #fffdf8;
    --ink: #1f1a14;
    --muted: #6b6257;
    --pass: #216e39;
    --fail: #a61b1b;
    --line: #d9cfbf;
    --accent: #9b6b1f;
  }
  * { box-sizing: border-box; }
  body {
    margin: 0;
    padding: 32px;
    font: 16px/1.5 Georgia, "Times New Roman", serif;
    color: var(--ink);
    background:
      radial-gradient(circle at top left, rgba(155, 107, 31, 0.12), transparent 24rem),
      linear-gradient(180deg, #f8f5ee 0%, var(--bg) 100%);
  }
  .rt-shell {
    max-width: 980px;
    margin: 0 auto;
    display: grid;
    gap: 20px;
  }
  .rt-card {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: 18px;
    padding: 20px 22px;
    box-shadow: 0 10px 30px rgba(31, 26, 20, 0.06);
  }
  .rt-title {
    margin: 0 0 4px;
    font-size: 2rem;
    line-height: 1.1;
  }
  .rt-subtle {
    margin: 0;
    color: var(--muted);
  }
  .rt-status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 0.85rem;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    border: 1px solid var(--line);
  }
  .rt-status.pass { color: var(--pass); border-color: rgba(33, 110, 57, 0.25); background: rgba(33, 110, 57, 0.08); }
  .rt-status.fail { color: var(--fail); border-color: rgba(166, 27, 27, 0.25); background: rgba(166, 27, 27, 0.08); }
  .rt-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 10px;
  }
  .rt-list li {
    padding: 10px 12px;
    border-radius: 12px;
    border: 1px solid var(--line);
    background: #fff;
    white-space: pre-wrap;
  }
  .rt-list li.pass { border-color: rgba(33, 110, 57, 0.18); }
  .rt-list li.fail { border-color: rgba(166, 27, 27, 0.18); }
  .rt-app {
    min-height: 72px;
    padding: 14px;
    border: 1px dashed var(--line);
    border-radius: 12px;
    background: #fff;
  }
  .rt-meta {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
  }
  code, pre {
    font: 13px/1.4 Consolas, "Courier New", monospace;
  }
  a { color: var(--accent); }
`;

function ensureStyles() {
  if (document.getElementById('rt-styles')) return;
  const style = document.createElement('style');
  style.id = 'rt-styles';
  style.textContent = styles;
  document.head.appendChild(style);
}

export function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

export function assertEqual(actual, expected, message) {
  if (!Object.is(actual, expected)) {
    throw new Error(`${message}\nExpected: ${String(expected)}\nActual: ${String(actual)}`);
  }
}

export function assertContains(actual, expectedPart, message) {
  if (!String(actual).includes(String(expectedPart))) {
    throw new Error(`${message}\nExpected to contain: ${String(expectedPart)}\nActual: ${String(actual)}`);
  }
}

export function click(el) {
  el.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));
}

export async function waitMicrotasks(count = 2) {
  for (let i = 0; i < count; i += 1) {
    await Promise.resolve();
  }
}

export async function waitFrames(count = 1) {
  for (let i = 0; i < count; i += 1) {
    await new Promise((resolve) => requestAnimationFrame(resolve));
  }
}

export async function waitFor(check, options = {}) {
  const timeoutMs = Number(options.timeoutMs ?? 1500);
  const intervalMs = Number(options.intervalMs ?? 20);
  const label = options.label ?? 'condition';
  const start = Date.now();
  let lastError = null;

  while ((Date.now() - start) < timeoutMs) {
    try {
      return await check();
    } catch (error) {
      lastError = error;
      await new Promise((resolve) => setTimeout(resolve, intervalMs));
    }
  }

  if (lastError) {
    throw new Error(`Timed out waiting for ${label}\n${lastError.message ?? String(lastError)}`);
  }

  throw new Error(`Timed out waiting for ${label}`);
}

export function createPage(title, subtitle = '') {
  ensureStyles();
  document.body.innerHTML = `
    <div class="rt-shell">
      <section class="rt-card">
        <div class="rt-meta">
          <h1 class="rt-title">${title}</h1>
          <span class="rt-status" id="rt-status">Running</span>
        </div>
        <p class="rt-subtle">${subtitle}</p>
      </section>
      <section class="rt-card">
        <h2>Assertions</h2>
        <ul class="rt-list" id="rt-results"></ul>
      </section>
      <section class="rt-card">
        <h2>Runtime DOM</h2>
        <div id="app" class="rt-app"></div>
      </section>
    </div>
  `;
  return {
    app: document.getElementById('app'),
    results: document.getElementById('rt-results'),
    status: document.getElementById('rt-status'),
  };
}

export async function runPage(title, subtitle, run) {
  const page = createPage(title, subtitle);
  let failures = 0;

  const record = (label, passed, details = '') => {
    const li = document.createElement('li');
    li.className = passed ? 'pass' : 'fail';
    li.textContent = passed ? `PASS: ${label}` : `FAIL: ${label}${details ? `\n${details}` : ''}`;
    page.results.appendChild(li);
    if (!passed) failures += 1;
  };

  const api = {
    app: page.app,
    record,
    assert,
    assertEqual,
    assertContains,
    click,
    waitMicrotasks,
    waitFrames,
    waitFor,
    step(label, fn) {
      return Promise.resolve()
        .then(fn)
        .then(() => record(label, true))
        .catch((error) => {
          record(label, false, error?.message ?? String(error));
          throw error;
        });
    },
  };

  try {
    await run(api);
  } catch (error) {
    record('page execution', false, error?.message ?? String(error));
    console.error(error);
  } finally {
    page.status.textContent = failures === 0 ? 'PASS' : 'FAIL';
    page.status.className = `rt-status ${failures === 0 ? 'pass' : 'fail'}`;
    document.title = `${title} - ${failures === 0 ? 'PASS' : 'FAIL'}`;
  }
}
