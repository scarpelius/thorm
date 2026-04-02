<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{
    el, text, cls, attrs, concat, read, state,
    on, onMount, repeat, item,
    inc, set, delay, task, push, val, html,
    client,
};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
\$count = state(0);
\$status = state('idle');
\$log = state([]);

\$app = el('div', [attrs(['class' => 'container p-3'])], [
    el('h1', [], [ text('Task action') ]),
    el('p', [], [ text('Queue actions with task, delay, and log updates.') ]),
    el('p', [], [ text(concat('Status: ', read(\$status))) ]),
    el('p', [], [ text(concat('Count: ', read(\$count))) ]),
    el('button', [
        cls('btn btn-primary me-2'),
        on('click', task([
            set(\$status, val('starting...'), true),
            push(\$log, concat('click #', read(\$count)), true),
            delay(300, [
                set(\$status, val('incremented'), true),
            ]),
            inc(\$count, 1, true),
            set(\$status, val('idle'), true),
        ]))
    ], [ text('Run task') ]),
    el('ul', [cls('mt-3')], [
        repeat(read(\$log), item(''), el('li', [], [ text(item('')) ]))
    ]),
    onMount([
        task([
            set(\$status, val('mounted'), true),
            push(\$log, val('mounted'), true),
            delay(200, [ set(\$status, val('idle'), true) ]),
        ])
    ])
]);
", true))]);

$count = state(0);
$status = state('idle');
$log = state([]);

$app = el('div', [attrs(['class' => 'container my-5'])], [
    el('div', [ cls('glass p-3 rounded-2') ], [
        el('h1', [], [ text('Task action') ]),
        el('p', [], [ text('Queue actions with task, delay, and log updates.') ]),
        el('p', [], [ text(concat('Status: ', read($status))) ]),
        el('p', [], [ text(concat('Count: ', read($count))) ]),
        el('button', [
            cls('btn btn-primary me-2'),
            on('click', task([
                set($status, val('starting...'), true),
                push($log, concat('click #', read($count)), true),
                delay(300, [
                    set($status, val('incremented'), true),
                ]),
                inc($count, 1, true),
                set($status, val('idle'), true),
            ]))
        ], [ text('Run task') ]),
        el('ul', [cls('mt-3')], [
            repeat(read($log), item(''), el('li', [], [ text(item('')) ]))
        ]),
        onMount([
            task([
                set($status, val('mounted'), true),
                push($log, val('mounted'), true),
                delay(200, [ set($status, val('idle'), true) ]),
            ])
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
    'template'      => __DIR__.'/../../assets/index.tpl.html',
    'opts'          => [
        'title'         => 'Task action',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
