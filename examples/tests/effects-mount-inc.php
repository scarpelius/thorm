<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function PhpJs\{
    el, text, attrs, cls, concat, read, state,
    inc,
    onMount,
    fragment
};
use PhpJs\Renderer;

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

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__ . '/../../public/tests/' . $test . '/';
if (!is_dir($path)) { mkdir($path, 0777, true); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'       => 'Effect mount + inc',
    'containerId' => 'app',
    'template'    => __DIR__ . '/../../assets/index-test.tpl.html',
]);

// save the bootstrap data (IR JSON)
$html_ok = file_put_contents($path . $res['iruri'], $res['irJson']) !== false;
// save the page
$page_ok = file_put_contents($path . 'index.html', $res['tpl']) !== false;

echo $html_ok ? green("Wrote html file\n") : red("Could not write html file\n");
echo $page_ok ? green("Wrote JSON data file\n") : red("Could not write JSON file\n");
echo "\n";
