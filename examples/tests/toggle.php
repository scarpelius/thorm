<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{el, text, show, inc, on, eq, read, state, cls, mod, client};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$open = state(0);
$app = el('div', [ cls('container my-5') ], [
    el('h2', [], [ text('Toggle Panel (MVP)') ]),
    el('button', [ cls('btn btn-danger'), on('click', inc($open, 1)) ], [ text('Toggle') ]),
    show(eq(mod(read($open), 2), 1), el('div', [ cls('border p-3 rounded text-bg-primary my-5') ], [ text('This panel is visible when open%2 === 1') ]))
]);


$app = client($app);

$renderer = new Render();
$res = $renderer->render($app);

$build = BuildExample::build([
    'name'          => strtolower(pathinfo(__FILE__, PATHINFO_FILENAME)),
    'path'          => __DIR__.'/../../public/tests/',
    'renderer'      => $res,
    'template'      => __DIR__.'/../../assets/index.tpl.html',
    'opts'          => [
        'title'         => 'Toggle Panel (MVP)',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
