<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{
    el, text, attrs, cls, concat, read, val, state, fragment,
    onVisible, onLeftViewport, selectorTarget,      // effect + target
    set                              // unified helper (use asAction = true)
};
use Thorm\Renderer;

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

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__ . '/../../public/tests/' . $test . '/';
if (!is_dir($path)) { mkdir($path, 0777, true); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'       => 'Effect visible + set',
    'containerId' => 'app',
    'template'    => __DIR__ . '/../../assets/index-test.tpl.html',
]);

$html_ok = file_put_contents($path . $res['iruri'], $res['irJson']) !== false;
$page_ok = file_put_contents($path . 'index.html', $res['tpl']) !== false;

echo $html_ok ? green("Wrote JSON data file\n") : red("Could not write JSON file\n");
echo $page_ok ? green("Wrote HTML page\n") : red("Could not write HTML page\n");
echo "\n";
