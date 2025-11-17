<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function PhpJs\{el, text, read, cls, state, item, repeat};
use PhpJs\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$items = state([
    ['id' => 1, 'name' => 'First item'],
    ['id' => 2, 'name' => 'Second item'],
    ['id' => 3, 'name' => 'Third item'],
    ['id' => 4, 'name' => 'Fourth item'],
]);

$item = el('li', [ cls('nav-link') ], [ text(item('name')) ]);

$app = el('div', [ cls('container') ], [
    el('h1', [], [ text('Repeat aka List')]),
    el('ul', [ cls('nav') ], [
        repeat(
            read($items),
            item('id'),
            $item
        ),
    ]),
]);

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));

$path = __DIR__.'/../../public/tests/'.$test.'/';
if(!is_dir($path)) { mkdir($path); }
$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'         => 'repeat aka List',
    'containerId'   => 'app',
    'template'      => __DIR__.'/../../assets/index-test.tpl.html',
    'iuri_dir'      => '/tests/'.$test,
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

