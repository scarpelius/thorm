<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function PhpJs\{el, text, concat, attrs, on, cls, set, num, read, state, ev, addTo};
use PhpJs\Renderer;

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
            on('click', addTo($count, read($step))) ], [ text('Inc by input') 
        ]),
        el('button', [
            cls('btn btn-danger'),
            on('click', set($count, 1000)) ], [ text('Reset to 1000')
        ])
    ]),
]);

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__.'/../../public/tests/'.$test.'/';
if(!is_dir($path)) { mkdir($path); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'         => 'Bid',
    'containerId'   => 'app',
    'template'      => __DIR__.'/../../assets/index-test.tpl.html',
]);
// save the bootstrap data
$html = file_put_contents($path . $res['iruri'], $res['irJson']);
// save the page
$json_data = file_put_contents(
    $path . 'index.html', 
    $res['tpl']
);

if($html !== false ) { echo green("Wrote html file\n"); } else { echo red("Bad luck, could not write html file.\n"); }
if($json_data !== false ) { echo green("Wrote JSON data file\n"); } else { echo red("Bad luck, could not write JSON file.\n"); }
echo "\n";

