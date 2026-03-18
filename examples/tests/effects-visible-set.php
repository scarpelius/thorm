<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{
    el, text, attrs, cls, concat, read, val, state, fragment,
    onVisible, onLeftViewport, selectorTarget,      // effect + target
    set                              // unified helper (use asAction = true)
    client,
};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

// ─────────────────────────────────────────────────────────────────────────────
// model

$status = state('waiting…');

// ─────────────────────────────────────────────────────────────────────────────
// UI (place a spacer so you need to scroll to see the sentinel)

$app = fragment([
    el('div', [attrs(['class' => 'container p-3'])], [
        el('h1', [], [ text('Effect: visible + set') ]),
        el('p', [ cls('sticky-top') ], [ text(concat('Status = ', read($status))) ]),
        el('p', [cls('text-muted')], [ text('Scroll down until the gray box enters the viewport.') ]),
        // spacer to force scroll
        el('div', [attrs(['style' => 'height: 1200px; background: repeating-linear-gradient(45deg,#f8f9fa,#f8f9fa 10px,#ffffff 10px,#ffffff 20px);'])], []),
        // sentinel
        el('div', [attrs(['id' => 'sentinel', 'style' => 'height: 120px; background: #e9ecef; display:flex; align-items:center; justify-content:center; border-radius:8px;'])], [
            text('👋 I am the sentinel — make me visible')
        ]),
        el('div', [attrs(['style' => 'height: 600px;'])], []), // extra space after
    ]),

    // EFFECT: when #sentinel becomes visible, set status
    onVisible(
        [ set($status, val('visible!'), true) ],   // asAction = true
        threshold: 0.1,
        rootMargin: '0px 0px -10% 0%',
        target: selectorTarget('#sentinel')
    ),
    // EFFECT: when #sentinel goes out of viewport, set status
    onLeftViewport(
        [ set($status, val('Invisible!'), true) ],   // asAction = true
        threshold: 0.1,
        rootMargin: '0px',
        target: selectorTarget('#sentinel')
    ),
    
]);

// ─────────────────────────────────────────────────────────────────────────────
// render → public/tests/<name>/


$app = client(el('div', [], [$app]));

$renderer = new Render();
$res = $renderer->render($app);

$build = BuildExample::build([
    'name'          => strtolower(pathinfo(__FILE__, PATHINFO_FILENAME)),
    'path'          => __DIR__.'/../../public/tests/',
    'renderer'      => $res,
    'template'      => __DIR__.'/../../assets/index-test.tpl.html',
    'opts'          => [
        'title'         => 'Effect visible + set',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
