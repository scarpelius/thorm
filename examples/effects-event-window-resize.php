<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use function Thorm\{
    el, text, attrs, cls, concat, read, state, fragment,
    onWindow,     // effect helper
    set, ev,      // unified helper (set(..., asAction=true)), event expr
    client,
};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

// ─────────────────────────────────────────────────────────────────────────────
// model

$winW = state(0);

// ─────────────────────────────────────────────────────────────────────────────
// UI

$app = fragment([
    el('div', [attrs(['class' => 'container p-3'])], [
        el('h1', [], [ text('Effect: event (window resize) + set') ]),
        el('p', [], [ text(concat('Window width = ', read($winW))) ]),
        el('p', [cls('text-muted')], [ text('Resize the browser window; value updates live.') ]),
    ]),

    // EFFECT: listen to window resize, set $winW from the event (window.innerWidth)
    onWindow('resize', [
        set($winW, ev('target.innerWidth'), true)   // asAction = true
    ]),
]);

// ─────────────────────────────────────────────────────────────────────────────
// render → public/tests/<name>/


$app = client(el('div', [], [$app]));

$renderer = new Render();
$res = $renderer->render($app);

$build = BuildExample::build([
    'name'          => strtolower(pathinfo(__FILE__, PATHINFO_FILENAME)),
    'path'          => __DIR__.'/../public/examples/',
    'renderer'      => $res,
    'template'      => __DIR__.'/../assets/index.tpl.html',
    'opts'          => [
        'title'         => 'Effect event window resize',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
