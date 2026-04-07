<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use function Thorm\{
    el, text, attrs, cls, concat, read, val, state, fragment,
    stringify,
    onMount,        // effect
    http,           // unified helper; we'll pass asAction = true
    client,
};
use Thorm\BuildExample;
use Thorm\Render;

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
        'title'         => 'Effect HTTP on mount',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
