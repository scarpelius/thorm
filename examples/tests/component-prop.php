<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function PhpJs\{el, text, fragment, slot, val, prop, component, cls, state, inc, read, on, concat};
use PhpJs\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }




$i = state(1);

// Optional: a template that also reads a prop, e.g. title
$CardTpl = fragment([
    el('div', [ cls('card mt-5') ], [
        el('h2', [], [ text(prop('title')) ]),  // reads ctx.props.title at runtime
        el('button', [ 
            cls('btn btn-primary col-3 mx-auto'),
            on('click', inc($i, 1)),
        ], [ text(val('Inc.')) ]),
        slot(),                                 // default body
    ]),
]);

// A component that passes PROPS and default slot content
$CardInstance = component($CardTpl, /* props */ [
    'title' => concat('Hello, Props! ', read($i)),
], [
    el('p', [], [ text(val('This is the card body.')) ]),
]);

// Compose a page IR that uses the three component instances
$app = el('div', [ cls('container') ], [
    $CardInstance,
    el('p', [ 
        cls('text-muted fs-1')
    ], [
        text(concat('i=', read($i)))
    ]),
]);




$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__.'/../../public/tests/'.$test.'/';
if(!is_dir($path)) { mkdir($path); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'         => 'Component - Props',
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
