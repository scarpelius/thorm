<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{el, text, concat, show, on, cls, eq, html, mod, read, state, inc, client};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
\$cnt = state(1);

\$app = el('div', [ cls('container mt-5') ], [
    el('h2', [cls('h4')], [
        text(concat('Count: ', read(\$cnt))),
        show(eq(mod(read(\$cnt), 2), 0), el('span', [ cls('') ], [ text(' is even') ])),
        show(eq(mod(read(\$cnt), 2), 1), el('span', [ cls('') ], [ text(' is odd') ])),
        show(eq(mod(read(\$cnt), 13), 0), el('p', [ cls('h6') ], [ text('How odd is that ðŸ™ƒ?') ])),
    ]),
    el('p', [], [text('A simple stateful counter that reacts to clicks and shows even/odd state.')]),
    el('button', [
        cls('btn btn-warning'),
        on('click', inc(\$cnt, 1))
    ], [ text('Inc') ]),
    show(eq(mod(read(\$cnt), 2), 0), el('span', [ cls('m-5 fs-5') ], [ text('Even!') ]))
]);
", true))]);

$cnt = state(1);

$app = el('div', [ cls('container mt-5') ], [
    el('h2', [cls('h4')], [
        text(concat('Count: ', read($cnt))),
        show(eq(mod(read($cnt), 2), 0), el('span', [ cls('') ], [ text(' is even') ])),
        show(eq(mod(read($cnt), 2), 1), el('span', [ cls('') ], [ text(' is odd') ])),
        show(eq(mod(read($cnt), 13), 0), el('p', [ cls('h6') ], [ text('How odd is that ðŸ™ƒ?') ])),
    ]),
    el('p', [], [text('A simple stateful counter that reacts to clicks and shows even/odd state.')]),
    el('button', [
        cls('btn btn-warning'),
        on('click', inc($cnt, 1))
    ], [ text('Inc') ]),
    show(eq(mod(read($cnt), 2), 0),
        el('span', [ cls('m-5 fs-5') ], [ text('Even!') ])),
    $code
]);

$app = client($app);

$renderer = new Render();
$res = $renderer->render($app);

$build = BuildExample::build([
    'name'          => strtolower(pathinfo(__FILE__, PATHINFO_FILENAME)),
    'path'          => __DIR__.'/../../public/tests/',
    'renderer'      => $res,
    'template'      => __DIR__.'/../../assets/index-test.tpl.html',
    'opts'          => [
        'title'         => 'Counter',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
