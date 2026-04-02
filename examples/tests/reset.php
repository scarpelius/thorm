<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{add, el, text, concat, attrs, on, cls, set, num, read, state, ev, client};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$count = state(42);
$step = state(1);
$increment = el('input', [ 
    cls('form-control'),
    attrs(['type' => 'number', 'value' => read($step)]),
    on('input', set($step, num(ev('target.value')))),
]);
$app = el('div', [cls('container')], [
    el('h1', [cls('mt-5')], [ text('Resettable Counter') ]),
    el('h2', [], [ text(concat('Count: ', read($count))) ]),
    $increment,
    el('div', [cls('d-flex justify-content-around p-5')], [
        el('button', [ 
            cls('btn btn-primary'), 
            on('click', add($count, read($step), true)) ], [ text('Inc by input') 
        ]),
        el('button', [
            cls('btn btn-danger'),
            on('click', set($count, 1000)) ], [ text('Reset to 1000')
        ])
    ]),
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
        'title'         => 'Bid',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
