# Thorm (php-js-primitives) (MVP)
Author front‑end UI with a tiny set of PHP primitives that compile to an **Intermediate Representation (IR)** and run in the browser using a minimal JS runtime.

> ⚠️ MVP: implements `state/read`, `val/eq/mod/add/concat`, `el`, `text`, `on(click)`, `show`, static `attr/cls/style`. More to come: `list`, `bind`, `task`, `route`.

## Install
```bash
composer require your-vendor/php-js-primitives
```

For local dev without Packagist, copy this folder into your project and ensure Composer autoloads it (or adjust your `composer.json`).

## Quickstart
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use function PhpJs\state;
use function PhpJs\el;
use function PhpJs\text;
use function PhpJs\on;
use function PhpJs\read;
use function PhpJs\concat;
use function PhpJs\mod;
use function PhpJs\eq;
use function PhpJs\show;
use function PhpJs\inc;
use PhpJs\Renderer;

$cnt = state(0);

$app = el('div', [], [
  el('h1', [], [ text(concat('Count: ', read($cnt))) ]),
  el('button', [ on('click', inc($cnt, 1)) ], [ text('Inc') ]),
  show(eq(mod(read($cnt), 2), 0), el('p', [], [ text('Even!') ]))
]);

$renderer = new Renderer();
echo $renderer->renderPage($app, [
  'title' => 'Counter MVP'
]);
```

Open the generated page in a browser. Clicking **Inc** updates the text and toggles the “Even!” paragraph when appropriate.

## Concepts
- **Atoms (`state`)**: reactive cells controlled by event actions like `inc()` and `set()`.
- **Expressions**: build derived values with `read`, `val`, `add`, `mod`, `eq`, `concat`.
- **Nodes**: `el`, `text`, `show` compose a small UI tree.
- **IR**: a JSON structure describing the UI and reactive wiring that the runtime executes.

## Status
- Runtime is intentionally tiny and readable for extension.
- The DSL avoids JS-in-strings; instead you compose with PHP helpers that map cleanly to runtime semantics.

## License
MIT
