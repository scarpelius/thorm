<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use function Thorm\{attrs, client, cls, component, concat, el, html, on, prop, read, set, state, text, val};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
\$title = state('Welcome');

\$LeafTpl = el('div', [cls('card shadow-sm')], [
    el('div', [cls('card-body')], [
        el('h3', [attrs(['id' => 'deep-title']), cls('card-title mb-2')], [
            text(prop('nestedTitle'))
        ]),
        el('p', [cls('text-muted mb-0')], [
            text(val('This title is read from a prop forwarded through another component.'))
        ]),
    ]),
]);

\$MiddleTpl = component(\$LeafTpl, [
    'nestedTitle' => prop('title'),
]);

\$NestedCard = component(\$MiddleTpl, [
    'title' => read(\$title),
]);

\$app = el('div', [cls('container py-4')], [
    el('div', [cls('d-flex gap-3 align-items-center mb-4')], [
        el('button', [
            cls('btn btn-primary'),
            on('click', set(\$title, val('Updated title')))
        ], [ text('Set updated title') ]),
        el('p', [cls('mb-0 text-muted')], [
            text(concat('Source atom: ', read(\$title)))
        ]),
    ]),
    \$NestedCard,
]);
", true))]);

$title = state('Welcome');

$LeafTpl = el('div', [cls('card shadow-sm')], [
    el('div', [cls('card-body')], [
        el('h3', [attrs(['id' => 'deep-title']), cls('card-title mb-2')], [
            text(prop('nestedTitle'))
        ]),
        el('p', [cls('text-muted mb-0')], [
            text(val('This title is read from a prop forwarded through another component.'))
        ]),
    ]),
]);

$MiddleTpl = component($LeafTpl, [
    'nestedTitle' => prop('title'),
]);

$NestedCard = component($MiddleTpl, [
    'title' => read($title),
]);

$app = el('div', [cls('container py-4')], [
    el('div', [cls('d-flex gap-3 align-items-center mb-4')], [
        el('button', [
            cls('btn btn-primary'),
            on('click', set($title, val('Updated title')))
        ], [ text('Set updated title') ]),
        el('p', [cls('mb-0 text-muted')], [
            text(concat('Source atom: ', read($title)))
        ]),
    ]),
    $NestedCard,
    $code,
]);

$app = client($app);

$renderer = new Render();
$res = $renderer->render($app);

$build = BuildExample::build([
    'name'          => strtolower(pathinfo(__FILE__, PATHINFO_FILENAME)),
    'path'          => __DIR__.'/../public/examples/',
    'renderer'      => $res,
    'template'      => __DIR__.'/../assets/index.tpl.html',
    'opts'          => [
        'title'         => 'Component - Nested',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
