<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{el, text, concat, inc, on, add, read, state, cls, html, client};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border')], [html(highlight_string("<?php
\$a = state(0); \$b = state(0);
\$app = client(el('div', [ cls('container') ], [
    el('h2', [], [ text('Two Counters + Sum') ]),
    el('div', [ cls('my-2') ], [
        el('div', [ cls('btn-group ') ], [
            el('button', [ cls('btn btn-primary'), on('click', inc(\$a, -1))], [ text('A--') ]),
            el('button', [cls('btn btn-primary'), on('click', inc(\$a, 1))], [ text('A++') ]),
        ]), 
        el('span', [], [ 
            text(concat(' A = ', read(\$a))) 
        ]) 
    ]),
    el('div', [ cls('my-2') ], [ 
        el('div', [ cls('btn-group ') ], [
            el('button', [ cls('btn btn-primary'), on('click', inc(\$b, -1)) ], [ text('B--') ]), 
            el('button', [ cls('btn btn-primary'), on('click', inc(\$b, 1)) ], [ text('B++') ]), 
        ]),
        el('span', [], [ text(concat(' B = ', read(\$b))) ]) 
    ]),
    el('h3', [], [ text(concat('Sum = ', add(\$a, read(\$b)))) ]),
]));
", true))]);

$a = state(0); $b = state(0);
$app = client(el('div', [ cls('container') ], [
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
    el('h3', [], [ text(concat('Sum = ', add($a, read($b)))) ]),
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
        'title'         => 'Two counters + Sum',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) { 
    echo green("File wrote to disk.\n"); 
} else { 
    echo red("Could not write files to disk.\n");
}
echo "\n";
