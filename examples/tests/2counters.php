<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function PhpJs\{el, text, concat, inc, on, add, read, state, cls, val};
use PhpJs\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$a = state(0); $b = state(0);
$app = el('div', [ cls('container') ], [
    el('h2', [], [ text('Two Counters + Sum') ]),
    el('div', [ cls('my-2') ], [
        el('div', [ cls('btn-group ') ], [
            el('button', [ cls('btn btn-primary'), on('click', inc($a, -1))], [ text('A--') ]),
            el('button', [cls('btn btn-primary'), on('click', inc($a, 1))], [ text('A++') ]),
        ]), 
        el('span', [], [ 
            text(concat(' A = ', read($a))) 
        ]) 
    ]),
    el('div', [ cls('my-2') ], [ 
        el('div', [ cls('btn-group ') ], [
            el('button', [ cls('btn btn-primary'), on('click', inc($b, -1)) ], [ text('B--') ]), 
            el('button', [ cls('btn btn-primary'), on('click', inc($b, 1)) ], [ text('B++') ]), 
        ]),
        el('span', [], [ text(concat(' B = ', read($b))) ]) 
    ]),
    el('h3', [], [ text(concat('Sum = ', add($a, read($b)))) ])
    //el('h3', [], [ text(add($a, read($b))) ])
]);

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__.'/../../public/tests/'.$test.'/';
if(!is_dir($path)) { mkdir($path); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'         => 'Two counters + Sum',
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

