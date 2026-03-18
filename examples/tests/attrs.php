<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{el, text, concat, style, on, cls, attrs, client, html, read, state, inc};

use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
\$cnt = state(0);
\$app = el('div', [ cls('container') ], [
    el('h2', [], [ text('Reactive attribute demo') ]),
    el('button', [ 
            attrs(['title' => concat('Clicked ', read(\$cnt), ' times')]), 
            cls('btn btn-primary'),
            style(['padding'=>'8px 12px','margin'=>'6px','border'=>'1px solid #ccc']),
            on('click', inc(\$cnt, 1)) 
        ], [ text('Hover me, then click') ]
    ),
    el('div', [], [ text(concat('Count: ', read(\$cnt))) ]),
]);
", true))]);

$cnt = state(0);
$app = client( el('div', [ cls('container') ], [
    el('h2', [], [ text('Reactive attribute demo') ]),
    el('button', [ 
            attrs(['title' => concat('Clicked ', read($cnt), ' times')]), 
            cls('btn btn-primary'),
            style(['padding'=>'8px 12px','margin'=>'6px','border'=>'1px solid #ccc']),
            on('click', inc($cnt, 1)) 
        ], [ text('Hover me, then click') ]
    ),
    el('div', [], [ text(concat('Count: ', read($cnt))) ]),
    $code
]));

$renderer = new Render();
$res = $renderer->render($app);

$build = BuildExample::build([
    'name'          => strtolower(pathinfo(__FILE__, PATHINFO_FILENAME)),
    'path'          => __DIR__.'/../../public/tests/',
    'renderer'      => $res,
    'template'      => __DIR__.'/../../assets/index-test.tpl.html',
    'opts'          => [
        'title'         => 'Bid',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) { 
    echo green("File wrote to disk.\n"); 
} else { 
    echo red("Could not write files to disk.\n");
}
echo "\n";
