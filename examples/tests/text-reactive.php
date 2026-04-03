<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{button, cls, concat, div, h1, html, inc, on, p, read, state, text, client};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = div([cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
\$count = state(1);

\$app = div([ cls('container') ], [
    h1([], [ text('Reactive text')]),
    p([], [ text('A minimal counter demo that updates text live when state changes.') ]),
    p([], [ text(concat('Count: ', read(\$count))) ]),
    button([
        on('click', inc(\$count, 1)),
        cls('btn btn-primary')
    ],[ 
        text('Inc') 
    ]),
]);
", true))]);

$count = state(1);

$app = div([ cls('container my-5') ], [
    div([cls('glass rounded-2 p-3')], [
        h1([], [ text('Reactive text')]),
        p([], [ text('A minimal counter demo that updates text live when state changes.') ]),
        p([], [ text(concat('Count: ', read($count))) ]),
        button([
            on('click', inc($count, 1)),
            cls('btn btn-primary')
        ],[
            text('Inc')
        ]),
    ]),
    $code
]);

$app = client($app);

$renderer = new Render();
$res = $renderer->render($app);

$build = BuildExample::build([
    'name'          => 'text-reactive',
    'path'          => __DIR__.'/../../public/tests/',
    'renderer'      => $res,
    'template'      => __DIR__.'/../../assets/index.tpl.html',
    'opts'          => [
        'title'         => 'Reactive text',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
