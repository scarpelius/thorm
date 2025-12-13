<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{
    el, text, attrs, cls, concat, read, state, fragment,
    onWindow,     // effect helper
    set, ev       // unified helper (set(..., asAction=true)), event expr
};
use Thorm\Renderer;

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

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__ . '/../../public/tests/' . $test . '/';
if (!is_dir($path)) { mkdir($path, 0777, true); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'       => 'Effect event window resize',
    'containerId' => 'app',
    'template'    => __DIR__ . '/../../assets/index-test.tpl.html',
]);

$html_ok = file_put_contents($path . $res['iruri'], $res['irJson']) !== false;
$page_ok = file_put_contents($path . 'index.html', $res['tpl']) !== false;

echo $html_ok ? green("Wrote JSON data file\n") : red("Could not write JSON file\n");
echo $page_ok ? green("Wrote HTML page\n") : red("Could not write HTML page\n");
echo "\n";
