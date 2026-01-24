<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{el, text, concat, read, on, cls, state, inc, html};
use Thorm\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
\$count = state(1);

\$app = el('div', [ cls('container') ], [
    el('h1', [], [ text('Reactive text')]),
    el('p', [], [ text(concat('Count: ', read(\$count))) ]),
    el('button',[
        on('click', inc(\$count, 1)),
        cls('btn btn-primary')
    ],[ 
        text('Inc') 
    ])
]);
", true))]);

$count = state(1);

$app = el('div', [ cls('container') ], [
    el('h1', [], [ text('Reactive text')]),
    el('p', [], [ text(concat('Count: ', read($count))) ]),
    el('button',[
        on('click', inc($count, 1)),
        cls('btn btn-primary')
    ],[ 
        text('Inc') 
    ]),
    $code
]);

$test = 'text-reactive';
$path = __DIR__.'/../../public/tests/'.$test.'/';
if(!is_dir($path)) { mkdir($path); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'         => 'Reactive text',
    'containerId'   => 'app',
    'template'      => __DIR__.'/../../assets/index-test.tpl.html',
    'iuri_dir'      => '/tests/'.$test,
]);

// save the bootstrap data
$json_data = file_put_contents($path . $res['iruri'], $res['irJson']);
// save the page
$html = file_put_contents(
    $path . 'index.html', 
    $res['tpl']
);

if($html !== false ) { echo green("Wrote html file\n"); } else { echo red("Bad luck, could not write html file.\n"); }
if($json_data !== false ) { echo green("Wrote JSON data file\n"); } else { echo red("Bad luck, could not write JSON file.\n"); }
echo "\n";
