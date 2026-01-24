<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{el, text, concat, repeat, on, read, state, cls, http, attrs, bind, item, html};
use Thorm\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
\$q = state('');
\$status = state(0);
\$results = state([]);

\$app = el('div', [ cls('container p-3') ], [
    el('input', [
        cls('form-input'),
        attrs(['placeholder' => 'Searchâ€¦']),
        ...bind(\$q, ['type' => 'text'])
    ]),
    el('button', [
        cls('btn btn-primary'),
        on('click', http(
            concat('/api/search/?search=', read(\$q)),
            'GET',
            \$results,
            \$status,
            null,
            null,
            'json'
        ))
    ], [ text('Search') ]),

    // status line
    el('p', [], [ text(concat('Status: ', read(\$status))) ]),

    // show results
    el('ul', [ cls('list-group') ], [
        repeat(read(\$results), item('id'),
            el('li', [cls('list-group-item')], [text(concat(item('title')))]),
        ),
    ])
]);
", true))]);

$q = state('');
$status = state(0);
$results = state([]);

$app = el('div', [ cls('container p-3') ], [
    el('input', [
        cls('form-input'),
        attrs(['placeholder' => 'Search…']),
        ...bind($q, ['type' => 'text'])
    ]),
    el('button', [
        cls('btn btn-primary'),
        on('click', http(
            concat('/api/search/?search=', read($q)),
            'GET',
            $results,
            $status,
            null,
            null,
            'json'
        ))
    ], [ text('Search') ]),

    // status line
    el('p', [], [ text(concat('Status: ', read($status))) ]),

    // show results
    el('ul', [ cls('list-group') ], [
        repeat(read($results), item('id'),
            el('li', [cls('list-group-item')], [text(concat(item('title')))]),
        ),
    ]),
    $code
]);

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__.'/../../public/tests/'.$test.'/';
if(!is_dir($path)) { mkdir($path); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'         => 'Search Box Live',
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
