<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use function Thorm\{
    el, text, attrs, cls, concat, read, val, state, fragment,
    after,        // effect helper (timeout)
    set, inc,     // unified helpers; pass asAction=true when used in Effects
    client,
};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

// ─────────────────────────────────────────────────────────────────────────────
// model

$status = state('waiting…');
$cnt    = state(0);

// ─────────────────────────────────────────────────────────────────────────────
// UI

$app = fragment([
    el('div', [attrs(['class' => 'container p-3'])], [
        el('h1', [], [ text('Effect: timeout + set/inc (once)') ]),
        el('p', [], [ text(concat('Status = ', read($status))) ]),
        el('p', [], [ text(concat('Counter = ', read($cnt))) ]),
        el('p', [cls('text-muted')], [ text('After ~1.5s, Status becomes "done" and Counter increments once.') ]),
    ]),

    // EFFECT: run once after 1500ms
    after(1500, [
        set($status, val('done'), true), // asAction = true
        inc($cnt, 1, true)
    ]),
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
        'title'         => 'Effect timeout + set/inc',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
