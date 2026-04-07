<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use function Thorm\{el, text, concat, attrs, on, cls, navigate, link, param, query, route, client};
use Thorm\BuildExample;
use Thorm\Render;

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


$app = client($app);

$renderer = new Render();
$res = $renderer->render($app);

$build = BuildExample::build([
    'name'          => strtolower(pathinfo(__FILE__, PATHINFO_FILENAME)),
    'path'          => __DIR__.'/../public/examples/',
    'renderer'      => $res,
    'template'      => __DIR__.'/../assets/index.tpl.html',
    'opts'          => [
        'title'         => 'Router',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
