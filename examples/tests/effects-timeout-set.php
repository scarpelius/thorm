<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{
    el, text, attrs, cls, concat, read, val, state, fragment,
    after,        // effect helper (timeout)
    set, inc      // unified helpers; pass asAction=true when used in Effects
};
use Thorm\Renderer;

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
// render → public/tests/<name>/

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__ . '/../../public/tests/' . $test . '/';
if (!is_dir($path)) { mkdir($path, 0777, true); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'       => 'Effect timeout + set/inc',
    'containerId' => 'app',
    'template'    => __DIR__ . '/../../assets/index-test.tpl.html',
]);

$html_ok = file_put_contents($path . $res['iruri'], $res['irJson']) !== false;
$page_ok = file_put_contents($path . 'index.html', $res['tpl']) !== false;

echo $html_ok ? green("Wrote JSON data file\n") : red("Could not write JSON file\n");
echo $page_ok ? green("Wrote HTML page\n") : red("Could not write HTML page\n");
echo "\n";
