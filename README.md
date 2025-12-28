# Thorm

PHP-first DSL for describing reactive UIs that compile to a small JavaScript runtime. Build views with simple PHP functions, emit an intermediate representation (IR), and let the runtime handle reactivity, events, routing, and effects in the browser.

## Project layout
- `src/functions.php`: Public DSL surface (state, el, attrs, on, repeat, route, component, effects, http, etc.).
- `src/Renderer.php`: Walks the IR tree, collects atoms, and emits `{atoms, root}` plus an HTML page using a template.
- `src/IR/*`: IR node definitions for atoms, expressions, actions, effects, and DOM nodes.
- `assets/index*.tpl.html`: HTML templates consumed by `Renderer` (`{$title}`, `{$containerId}`, `{$runtimeSrc}`, `{$iruri}` placeholders).
- `assets/runtime/**`: Browser runtime (core, primitives, utils, devtools). Synced to `public/runtime/` when shipping.
- `examples/tests/*.php`: End-to-end samples that generate pages under `public/tests/` (ignore `examples/tests/hronic/`, it is outdated).
- `cli/watch.sh`: Dev helper that watches the tree, reruns touched example PHP files, and syncs runtime assets.

## Requirements
- PHP 8.1+
- Composer (for autoloading via `vendor/autoload.php`)
- A static file server to view the generated pages (e.g., `php -S localhost:8000 -t public`)

## Quick start
1) Install dependencies:
```bash
composer install
```
2) Run an example to generate IR + HTML in `public/tests/<example>/`:
```bash
php examples/tests/counter.php
```
3) Serve the `public/` folder and open `http://localhost:8000/tests/counter/`.

To regenerate on changes, use the watcher (auto-runs example scripts and syncs runtime):
```bash
bash cli/watch.sh
```
(Use `WATCH_MODE=poll` on filesystems without inotify.)

## Authoring UIs in PHP
Build views with the DSL from `Thorm\` (autoloaded via composer files):
```php
<?php
use Thorm\Renderer;
use function Thorm\{state, el, text, attrs, cls, on, inc, read};

$cnt = state(0);
$app = el('div', [cls('p-3')], [
    el('h1', [], [text('Counter')]),
    el('p', [], [text(read($cnt))]),
    el('button', [attrs(['class' => 'btn btn-primary']), on('click', inc($cnt, 1))], [text('Increment')]),
]);

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title' => 'Counter',
    'containerId' => 'app',
    'template' => __DIR__ . '/assets/index.tpl.html',
]);
file_put_contents(__DIR__ . '/public/tests/counter/' . $res['iruri'], $res['irJson']);
file_put_contents(__DIR__ . '/public/tests/counter/index.html', $res['tpl']);
```
`renderPage()` returns the IR JSON (atoms + root) and an HTML page with the runtime bootstrap embedded. Serve the output and the runtime under `public/runtime`.

## DSL highlights (see `src/functions.php`)
- State & expressions: `state`, `read`, `val/num/str/not/concat/cond`, `item` inside `repeat` (`item('')` returns the full item).
- DOM nodes: `el`, `text`, `fragment`, `show`, `repeat` (lists), `attrs`, `cls`, `style`, `on` (events).
- Routing & links: `route` with path table + fallback, `link`, `navigate`, `redirect`, `param`, `query`.
- Components & slots: `component`, `slot`, `prop` for props + named or default slots.
- Effects & actions: `effect`, `onMount`, `watch`, `every`, `after`, `onVisible`, `onWindow`, `onDocument`, `onSelf`, `selectorTarget`, `windowTarget`, `documentTarget`.
- HTTP helper: `http(url, method, toAtom, statusAtom, headers, body, parse)` usable as listener or action (`asAction=true`).
- Utilities: `bind` for two-way form binding; `inc`, `set`, `add`, `delay` for state updates.

## Examples worth reading (`examples/tests`)
- `counter.php`, `2counters.php`: Atoms, math expressions, conditional rendering.
- `attrs.php`, `text-reactive.php`, `toggle.php`: Props, class/style helpers, simple state.
- `repeat.php`: Lists with keyed templates and `item()` accessors.
- `components.php`, `component-prop.php`, `components-style-classes.php`, `components-live*.php`: Components with props + slots.
- `effects-*`, `events.php`: Effects (mount, interval, timeout, visible, watch), HTTP actions, window/document events, navigation.
- `router.php`, `fragments.php`, `search-box-live.php`, `bid.php`: Routing, fragments, form bindings, more realistic flows.

## Runtime & templates
- Runtime entry: `assets/runtime/index.js` (bundles core/primitives/utils/devtools) and is expected to be available at `/assets/Thorm-runtime.js` or copied to `public/runtime/`.
- Templates: `assets/index.tpl.html` (default), `assets/index-test.tpl.html`, `assets/index-old.tpl.html`, `assets/index-hronic.tpl.html` (legacy). Tokens are replaced by `Renderer::renderPage()`.

## Serving and packaging
- For quick demos, serve `public/` with PHP's built-in server: `php -S localhost:8000 -t public`.
- When deploying, copy `public/` plus the generated `*.ir.json` files. Ensure runtime assets from `assets/runtime/` (or `public/runtime/`) are reachable at the path used in the template.

## Notes
- The `examples/tests/hronic/` folder and `README.md.old` are deprecated and intentionally ignored.
- IR is JSON-friendly; you can persist it alongside rendered HTML or feed it to another build step.
