<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function PhpJs\{
    el, text, attrs, cls, concat, read, val, state, fragment,
    stringify,
    onMount,        // effect
    http            // unified helper; we'll pass asAction = true
};
use PhpJs\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

// ─────────────────────────────────────────────────────────────────────────────
// model

$bodyAtom    = state(null);
$statusAtom  = state(null);
$headersAtom = state(null);

// ─────────────────────────────────────────────────────────────────────────────
// UI

$app = fragment([
    el('div', [attrs(['class' => 'container p-3'])], [
        el('h1', [], [ text('Effect: HTTP on mount') ]),
        el('p', [], [ text(concat('HTTP status = ', read($statusAtom))) ]),
        el('p', [], [ text('Body:') ]),
        el('pre', [cls('bg-light p-2')], [ text(read($bodyAtom)) ]),
        el('p', [cls('mt-2')], [ text('Response Headers:') ]),
        el('pre', [cls('bg-light p-2')], [ text(read($headersAtom)) ]),
        el('p', [cls('text-muted mt-3')], [
            text('This runs once on mount. Adjust the URL if your dev server exposes a different endpoint.')
        ]),
    ]),

    // EFFECT: perform GET and store result atoms (body as text for easy display)
    onMount([
        // url, method, to, status, headers, body, parse, asAction=true
        http(
            val('/api/ping/'), 
            'GET', 
            $bodyAtom, 
            $statusAtom, 
            [ 'Content-Type' => 'application/x-www-form-urlencoded' ], 
            null, 
            'text', 
            true, 
            $headersAtom
        )
    ]),
]);

// ─────────────────────────────────────────────────────────────────────────────
// render → public/tests/<name>/

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__ . '/../../public/tests/' . $test . '/';
if (!is_dir($path)) { mkdir($path, 0777, true); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'       => 'Effect HTTP on mount',
    'containerId' => 'app',
    'template'    => __DIR__ . '/../../assets/index-test.tpl.html',
]);

$html_ok = file_put_contents($path . $res['iruri'], $res['irJson']) !== false;
$page_ok = file_put_contents($path . 'index.html', $res['tpl']) !== false;

echo $html_ok ? green("Wrote JSON data file\n") : red("Could not write JSON file\n");
echo $page_ok ? green("Wrote HTML page\n") : red("Could not write HTML page\n");
echo "\n";
