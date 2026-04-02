<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{
    el, text, attrs, cls, concat, read, state,
    inc,
    onMount,
    fragment,
    client,
};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

// ─────────────────────────────────────────────────────────────────────────────
// model

$cnt = state(0);

// ─────────────────────────────────────────────────────────────────────────────
// UI + Effect
//
// Expectation: After mount, $cnt becomes 1 without any user interaction.

$app = fragment([
    el('div', [attrs(['class' => 'container p-3'])], [
        el('h1', [], [ text('Effect: mount + inc') ]),
        el('p', [], [ text(concat('Count = ', read($cnt))) ]),
        el('p', [cls('text-muted')], [ text('Should read 13 on first paint.') ]),
    ]),

    // EFFECT: on mount, increment the counter once
    onMount([ inc($cnt, 13, true) ]),
]);

// ─────────────────────────────────────────────────────────────────────────────
// render → public/tests/<filename>/


$app = client(el('div', [], [$app]));

$renderer = new Render();
$res = $renderer->render($app);

$build = BuildExample::build([
    'name'          => strtolower(pathinfo(__FILE__, PATHINFO_FILENAME)),
    'path'          => __DIR__.'/../../public/tests/',
    'renderer'      => $res,
    'template'      => __DIR__.'/../../assets/index.tpl.html',
    'opts'          => [
        'title'         => 'Effect mount + inc',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
