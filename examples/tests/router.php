<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function PhpJs\{el, text, concat, attrs, on, cls, navigate, link, param, query, route};
use PhpJs\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$base = '/tests/router/';

$Home = el('div', [], [
    el('h1', [], [ text('Home') ]),
    link($base.'search?q=chair', [ cls('btn btn-primary m-3') ], [ text('Search "chair"') ]),
    el('p', [], [ text('Go to Auction 42: '), link($base.'auction/42', [], [ text('open') ]), ]),
]);

$Auction = el('div', [], [
    el('h1', [], [ text(concat('Auction ', param('id'))) ]),
    el('button', [ 
        cls('btn btn-primary m-3'),
        on('click', navigate($base)),
        attrs(['id'=>'back-button']),
    ], [ text('Back') ]),
    // programmatic back to search preset:
    el('button', [ 
        cls('btn btn-warning'),
        on('click', navigate(concat($base, 'search?q=table'))),
        attrs(['id'=>'search-button']), 
    ], [ text('Search tables') ]),
]);

$Search = el('div', [], [
    el('h1', [], [ text('Search') ]),
    el('p', [], [ text(concat('q=', query('q'))) ]),
    link($base, [cls('btn btn-primary m-3')], [ text('Home') ]),
]);

$NotFound = el('h1', [], [ text('Page Not Found.. why oh why???') ]);

$app = el('div' ,[ cls('container') ], [
    route([
        $base                   => $Home,
        $base . 'auction/:id'   => $Auction,
        $base . 'search'        => $Search,
    ], $NotFound),
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
