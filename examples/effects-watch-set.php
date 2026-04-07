<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use function Thorm\{
    el, text, attrs, cls, concat, read, val, state, fragment,
    // listeners (props)
    on, inc, ev, num,
    // effects
    watch,
    // actions (via unified helpers with $asAction = true)
    set,
    html,
    client,
};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

// ─────────────────────────────────────────────────────────────────────────────
// model

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
\$count      = state(0);
\$label      = state('idle');
\$delay      = state(300);
\$throttle   = state(500);

\$app = fragment([
    el('div', [attrs(['class' => 'container p-3'])], [
        el('h1', [], [ text('Effect: watch + set (debounced)') ]),
        el('div', [ cls('row') ], [
            el('label', [ attrs([ 'for' => 'debounce' ]) ], [ 
                el('a', [
                    attrs([
                        'href'  => 'https://developer.mozilla.org/en-US/docs/Glossary/Debounce',
                        'rel'   => 'nofollow',
                        'target' => '_blank',
                    ]),
                ], [text('Debounce')]),
             ]),
            el('input', [ 
                attrs([
                    'id'    => 'debounce', 
                    'type'  => 'range', 
                    'min'   => 0, 
                    'max'   => 3000,
                    'value' => read(\$delay)
                ]),
                on('change', set(\$delay, ev('target.value'))),
             ], []),
        ]),
        el('div', [ cls('row') ], [
            el('label', [ attrs([ 'for' => 'throttle' ]) ], [ 
                el('a', [
                    attrs([
                        'href'  => 'https://developer.mozilla.org/en-US/docs/Glossary/Throttle',
                        'rel'   => 'nofollow',
                        'target' => '_blank',
                    ]),
                ], [text('Throttle')]),
            ]),
            el('input', [ 
                attrs([
                    'id'    => 'throttle', 
                    'type'  => 'range', 
                    'min'   => 0, 
                    'max'   => 3000,
                    'value' => read(\$throttle)
                ]),
                on('change', set(\$throttle, ev('target.value'))),
             ], []),
        ]),
        el('p', [], [ text(concat('Count = ', read(\$count))) ]),
        el('p', [], [ text(concat('Label = ', read(\$label))) ]),
        el('div', [cls('row gap-2 my-2 d-flex justify-content-center')], [
            el('button', [
                cls('btn btn-primary col-4'),
                on('click', inc(\$count, 1)) // normal Listener path (props)
            ], [ text('Inc') ]),
            el('button', [
                cls('btn btn-secondary col-4'),
                on('click', inc(\$count, -1))
            ], [ text('Dec') ]),
        ]),
        el('p', [cls('text-muted')], [
            text(concat('Clicking Inc/Dec quickly should update Label after ', read(\$delay), 'ms debounce.'))
        ]),
    ]),

    // EFFECT: watch \$count; update \$label to \"Count: <n>\" (debounced 300ms)
    watch(
        read(\$count),
        [ set(\$label, concat('Count: ', read(\$count)), true) ], // <- asAction = true
        immediate: true,
        debounceMs: read(\$delay),
        throttleMs: read(\$throttle),
    ),
]);
", true))]);

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
            el('label', [ attrs([ 'for' => 'debounce' ]) ], [ 
                el('a', [
                    attrs([
                        'href'  => 'https://developer.mozilla.org/en-US/docs/Glossary/Debounce',
                        'rel'   => 'nofollow',
                        'target' => '_blank',
                    ]),
                ], [text('Debounce')]),
             ]),
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
            el('label', [ attrs([ 'for' => 'throttle' ]) ], [ 
                el('a', [
                    attrs([
                        'href'  => 'https://developer.mozilla.org/en-US/docs/Glossary/Throttle',
                        'rel'   => 'nofollow',
                        'target' => '_blank',
                    ]),
                ], [text('Throttle')]),
            ]),
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
        $code
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
// render → public/examples/<name>/


$app = client(el('div', [], [$app]));

$renderer = new Render();
$res = $renderer->render($app);

$build = BuildExample::build([
    'name'          => strtolower(pathinfo(__FILE__, PATHINFO_FILENAME)),
    'path'          => __DIR__.'/../public/examples/',
    'renderer'      => $res,
    'template'      => __DIR__.'/../assets/index.tpl.html',
    'opts'          => [
        'title'         => 'Effect watch + set',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
