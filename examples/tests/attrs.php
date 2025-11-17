<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function PhpJs\{el, text, concat, style, on, cls, attrs, read, state, inc};
use PhpJs\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$cnt = state(0);
$app = el('div', [ cls('container') ], [
    el('h2', [], [ text('Reactive attribute demo') ]),
    el('button', [ 
        attrs(['title' => concat('Clicked ', read($cnt), ' times')]), 
        cls('btn btn-primary'),
        style(['padding'=>'8px 12px','margin'=>'6px','border'=>'1px solid #ccc']),
        on('click', inc($cnt, 1)) 
    ],
    [ text('Hover me, then click') ]
  ),
  el('div', [], [ text(concat('Count: ', read($cnt))) ])
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

