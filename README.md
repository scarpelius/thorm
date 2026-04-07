# Thorm

PHP-first DSL for describing reactive UIs that compile to a small JavaScript runtime. Build views with simple PHP functions, emit an intermediate representation (IR), and let the runtime handle reactivity, events, routing, and effects in the browser.

## Project docs
- [ROADMAP.md](ROADMAP.md): Current project direction, priorities, and release phases.
- [CONTRIBUTING.md](CONTRIBUTING.md): Current contribution policy for the public repo.
- [SECURITY.md](SECURITY.md): How to report security issues privately.

## Current release status
Thorm is not yet published as a public Composer package.

- In this repository, `composer install` is only used to generate local autoload files under `vendor/`.
- A public GitHub repository is not required for that local install flow.
- A public repository matters later for `composer require bitforge/thorm` from another project or for Packagist publication.
- Until the repository is public, consume Thorm from this checkout or through a Composer `path` repository in a separate local test project.

## Project layout
- `src/php/functions.php`: Public DSL surface (state, el, attrs, on, repeat, route, component, effects, http, etc.).
- `src/php/Render.php`: Builds the shared IR and can render server-side HTML from that IR.
- `src/php/BuildExample.php`: Writes generated example output to `public/examples/<example>/`.
- `src/php/IR/*`: IR node definitions for atoms, expressions, actions, effects, and DOM nodes.
- `assets/index.tpl.html`: HTML template used during example generation (`{$title}`, `{$containerId}`, `{$iruri}` placeholders).
- `public/runtime/**`: Browser runtime (core, primitives, utils, devtools).
- `examples/*.php`: End-to-end samples that generate pages under `public/examples/`.
- `cli/watch.sh`: Dev helper that watches the tree, reruns touched example PHP files, and syncs runtime assets.

## Requirements
- PHP 8.1+
- Composer (or an existing `vendor/` directory) for autoloading via `vendor/autoload.php`
- A static file server to view the generated pages (e.g., `php -S localhost:8000 -t public`)

## Quick start
1) Install autoload files in this repository:
```bash
composer install
```
2) Run an example to generate IR + HTML in `public/examples/<example>/`:
```bash
php examples/counter.php
```
3) Serve the `public/` folder and open `http://localhost:8000/examples/counter/`.

To regenerate on changes, use the watcher (auto-runs example scripts and syncs runtime):
```bash
bash cli/watch.sh
```
(Use `WATCH_MODE=poll` on filesystems without inotify.)

## Testing from another local project before publication
If you want to exercise the package as a dependency before the repository is public, use a Composer `path` repository:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../Thorm"
    }
  ],
  "require": {
    "bitforge/thorm": "*"
  }
}
```

That lets you validate package metadata, autoloading, and install behavior locally before making the GitHub repository public.

## Authoring UIs in PHP
Build views with the DSL from `Thorm\` (autoloaded via composer files):
```php
<?php
use Thorm\BuildExample;
use Thorm\Render;
use function Thorm\{state, el, text, attrs, cls, on, inc, read, client};

$cnt = state(0);
$app = el('div', [cls('p-3')], [
    el('h1', [], [text('Counter')]),
    el('p', [], [text(read($cnt))]),
    el('button', [attrs(['class' => 'btn btn-primary']), on('click', inc($cnt, 1))], [text('Increment')]),
]);

$app = client($app);

$render = new Render();
$res = $render->render($app);

BuildExample::build([
    'name' => 'counter',
    'path' => __DIR__ . '/public/examples/',
    'renderer' => $res,
    'template' => __DIR__ . '/assets/index.tpl.html',
    'opts' => [
        'title' => 'Counter',
        'containerId' => 'app',
    ],
]);
```
`Render::render()` returns the IR plus server-rendered HTML. `BuildExample::build()` writes the IR JSON and `index.html` for the example. Serve the output and the runtime under `public/runtime`.

## DSL highlights (see `src/php/functions.php`)
- State & expressions: `state`, `read`, `val/num/str/not/concat/cond`, `item` inside `repeat` (`item('')` returns the full item).
- DOM nodes: `el`, `text`, `fragment`, `show`, `repeat` (lists), `attrs`, `cls`, `style`, `on` (events).
- Routing & links: `route` with path table + fallback, `link`, `navigate`, `redirect`, `param`, `query`.
- Components & slots: `component`, `slot`, `prop` for props + named or default slots.
- Effects & actions: `effect`, `onMount`, `watch`, `every`, `after`, `onVisible`, `onWindow`, `onDocument`, `onSelf`, `selectorTarget`, `windowTarget`, `documentTarget`.
- HTTP helper: `http(url, method, toAtom, statusAtom, headers, body, parse)` usable as listener or action (`asAction=true`).
- Utilities: `bind` for two-way form binding; `inc`, `set`, `add`, `delay` for state updates.

## Examples worth reading (`examples`)
- `counter.php`, `2counters.php`: Atoms, math expressions, conditional rendering.
- `attrs.php`, `text-reactive.php`, `toggle.php`: Props, class/style helpers, simple state.
- `repeat.php`: Lists with keyed templates and `item()` accessors.
- `components.php`, `component-prop.php`, `components-style-classes.php`, `components-live*.php`: Components with props + slots.
- `effects-*`, `events.php`: Effects (mount, interval, timeout, visible, watch), HTTP actions, window/document events, navigation.
- `router.php`, `fragments.php`, `search-box-live.php`, `bid.php`: Routing, fragments, form bindings, more realistic flows.

## Runtime & templates
- Runtime entry: `public/runtime/index.js`.
- Templates: `assets/index.tpl.html`. Tokens are replaced during example generation.

## Serving and packaging
- For quick demos, serve `public/` with PHP's built-in server: `php -S localhost:8000 -t public`.
- When deploying, copy `public/` plus the generated `*.ir.json` files. Ensure runtime assets under `public/runtime/` stay reachable at the path used in the generated page.

## Notes
- IR is JSON-friendly; you can persist it alongside rendered HTML or feed it to another build step.
