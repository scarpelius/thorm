<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{el, text, read, cls, html, state, item, repeat, val, client};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
    \$items = state([
    ['id' => 1, 'name' => 'First item'],
    ['id' => 2, 'name' => 'Second item'],
    ['id' => 3, 'name' => 'Third item'],
    ['id' => 4, 'name' => 'Fourth item'],
]);

\$item = el('li', [ cls('nav-link') ], [ text(item('name')) ]);

\$app = el('div', [ cls('container my-5') ], [
    el('h1', [], [ text('Repeat aka List')]),
    el('ul', [ cls('nav') ], [
        repeat(
            read(\$items),
            item('id'),
            \$item
        ),
    ]),
]);
", true))]);

$items = state([
    ['id' => 1, 'name' => 'First item'],
    ['id' => 2, 'name' => 'Second item'],
    ['id' => 3, 'name' => 'Third item'],
    ['id' => 4, 'name' => 'Fourth item'],
]);

$item = el('li', [ cls('nav-link') ], [ text(item('name')) ]);

$app = el('div', [ cls('container my-5') ], [
    el('div', [ cls('glass p-3 rounded-2') ], [
        el('h1', [], [ text('Repeat aka List')]),
        el('p', [], [text('Render reactive lists with keyed repeat templates.')]),
        el('ul', [ cls('nav') ], [
            repeat(
                read($items),
                item('id'),
                $item
            ),
        ]),
    ]),
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
        'title'         => 'repeat aka List',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
