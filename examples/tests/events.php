<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{el, text, attrs, inc, on, concat, read, state, cls, num, add, set, ev,
    client,
    onMount, watch, http, every, onWindow, onVisible, navigate, selectorTarget, fragment  };
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

// ─────────────────────────────────────────────────────────────────────────────
// model (atoms)

$cnt     = state(0);
$tick    = state(0);
$winW    = state(0);
$resp    = state(null);
$status  = state(null);

// ─────────────────────────────────────────────────────────────────────────────
// UI

$ui = el('div', [attrs(['class' => 'container p-3'])], [
    el('h1', [], [ text(concat('Effects demo — count=', read($cnt))) ]),
    el('p',  [], [ text(concat('Window width: ', read($winW))) ]),
    el('p',  [], [ text(concat('Tick: ', read($tick))) ]),

    el('div', [attrs(['class' => 'row gap-2 my-2'])], [
        el('button',
            [ attrs(['class' => 'col-3 btn btn-primary']),
              on('click', inc($cnt, 1)) ],
            [ text('Inc') ]),

        el('button',
            [ attrs(['class' => 'col-3 btn btn-secondary']),
              on('click', inc($cnt, 5)) ],
            [ text('Add 5') ]),
    ]),

    el('div', [attrs(['style' => 'height: 1000px;'])], []),

    el('div', [attrs(['id' => 'lazy', 'class' => 'my-3'])], [
        text('Scroll this into view to trigger onVisible()…'),
    ]),

    el('pre', [attrs(['class' => 'mt-3 bg-light p-2'])], [
        text(concat('Last HTTP status: ', read($status))),
    ]),
]);

// ─────────────────────────────────────────────────────────────────────────────
// Effects (as non-visual nodes; place them alongside UI inside a fragment)

// 1) On mount, bump the counter once.
$fxOnMount = onMount([ inc($cnt, 1, true) ]);

// 2) Watch count; when it changes, ping an endpoint (debounced), store result.
$fxWatchCount = watch(
    read($cnt),
    [
        http(
            // GET /api/ping?c=<cnt>
            concat('/api/ping?c=', read($cnt)),
            'GET',
            $resp,      // to
            $status,    // status
            null,       // headers
            null,       // body
            'text',     // parse
            true
        )
    ],
    immediate: false,
    debounceMs: 400
);

// 3) Interval: every 1s bump the tick.
$fxEvery = every(1000, [ inc($tick, 1, true) ]);

// 4) Window resize: update winW using the DOM event’s value.
$fxWindow = onWindow('resize', [ set($winW, ev('target.innerWidth'), true) ]);

// 5) Visible: when #lazy becomes visible, navigate somewhere (demo).
$fxVisible = onVisible(
    [
        // navigate to a route reflecting current count (pure demo)
        navigate(concat('/tests/router/search?q=', read($cnt)), true)
    ],
    threshold: 0.1,
    rootMargin: '0px 0px -10% 0px',
    target: selectorTarget('#lazy')
);

// Optional: after a delay, do something (e.g., status banner clear)
// $fxAfter = after(3000, [ set($status, val(null)) ]);

// Group everything in one fragment:
$app = fragment([
    $ui,
    $fxOnMount,
    $fxWatchCount,
    $fxEvery,
    $fxWindow,
    $fxVisible,
    // $fxAfter,
]);



$app = client(el('div', [], [$app]));

$renderer = new Render();
$res = $renderer->render($app);

$build = BuildExample::build([
    'name'          => strtolower(pathinfo(__FILE__, PATHINFO_FILENAME)),
    'path'          => __DIR__.'/../../public/tests/',
    'renderer'      => $res,
    'template'      => __DIR__.'/../../assets/index-test.tpl.html',
    'opts'          => [
        'title'         => 'Effects (MVP)',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
