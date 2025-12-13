<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{el, text, concat, val, on, cls, attrs, read, state, http, cond, get, bind};
use Thorm\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$cnt = state(0);

$q = state('');
$status = state(0);
$results = state([]);

$amount = state(10);
$status = state(0);
$out = state('');

$app = el('form', [
    cls('m-5'),
    on('submit', http(
        val('/api/bid/'),
        'POST',
        $out,
        $status,
        ['Content-Type' => 'application/x-www-form-urlencoded'],
        concat('amount=', read($amount)),
        'json'
    ))
], [
    el('input', [ cls('form-control'), attrs(['type'=>'number']), ...bind($amount, ['type'=>'number']) ]),
    el('button', [ cls('btn btn-primary') ], [ text('Place bid') ]),
    el('p', [], [ text(get(read($out), 'message')) ]),

    el('p', [], [
        text(
            cond(
                get(read($out), 'ok'),
                'Thanks!',
                get(get(read($out), 'error'), 'message')
            )
        )
    ])
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

