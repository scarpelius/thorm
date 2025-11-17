<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function PhpJs\{
    el, text, attrs, cls, concat, read, val, state, fragment,
    // listeners (props)
    on, inc, ev, num,
    // effects
    watch,
    // actions (via unified helpers with $asAction = true)
    set
};
use PhpJs\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

// ─────────────────────────────────────────────────────────────────────────────
// model

$count      = state(0);
$label      = state('idle');
$delay      = state(300);
$throttle   = state(500);

// ─────────────────────────────────────────────────────────────────────────────
// UI

$app = fragment([
    el('div', [attrs(['class' => 'container p-3'])], [
        el('h1', [], [ text('Effect: watch + set (debounced)') ]),
        el('div', [ cls('row') ], [
            el('label', [ attrs([ 'for' => 'debounce' ]) ], [ text('Debounce') ]),
            el('input', [ 
                attrs([
                    'id'    => 'debounce', 
                    'type'  => 'range', 
                    'min'   => 0, 
                    'max'   => 3000,
                    'value' => read($delay)
                ]),
                on('change', set($delay, ev('target.value'))),
             ], []),
        ]),
        el('div', [ cls('row') ], [
            el('label', [ attrs([ 'for' => 'throttle' ]) ], [ text('Throttle') ]),
            el('input', [ 
                attrs([
                    'id'    => 'throttle', 
                    'type'  => 'range', 
                    'min'   => 0, 
                    'max'   => 3000,
                    'value' => read($throttle)
                ]),
                on('change', set($throttle, ev('target.value'))),
             ], []),
        ]),
        el('p', [], [ text(concat('Count = ', read($count))) ]),
        el('p', [], [ text(concat('Label = ', read($label))) ]),
        el('div', [cls('row gap-2 my-2 d-flex justify-content-center')], [
            el('button', [
                cls('btn btn-primary col-4'),
                on('click', inc($count, 1)) // normal Listener path (props)
            ], [ text('Inc') ]),
            el('button', [
                cls('btn btn-secondary col-4'),
                on('click', inc($count, -1))
            ], [ text('Dec') ]),
        ]),
        el('p', [cls('text-muted')], [
            text(concat('Clicking Inc/Dec quickly should update Label after ', read($delay), 'ms debounce.'))
        ]),
    ]),

    // EFFECT: watch $count; update $label to "Count: <n>" (debounced 300ms)
    watch(
        read($count),
        [ set($label, concat('Count: ', read($count)), true) ], // <- asAction = true
        immediate: true,
        debounceMs: read($delay),
        throttleMs: read($throttle),
    ),
]);

// ─────────────────────────────────────────────────────────────────────────────
// render → public/tests/<name>/

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__ . '/../../public/tests/' . $test . '/';
if (!is_dir($path)) { mkdir($path, 0777, true); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'       => 'Effect watch + set',
    'containerId' => 'app',
    'template'    => __DIR__ . '/../../assets/index-test.tpl.html',
]);

$html_ok = file_put_contents($path . $res['iruri'], $res['irJson']) !== false;
$page_ok = file_put_contents($path . 'index.html', $res['tpl']) !== false;

echo $html_ok ? green("Wrote JSON data file\n") : red("Could not write JSON file\n");
echo $page_ok ? green("Wrote HTML page\n") : red("Could not write HTML page\n");
echo "\n";
