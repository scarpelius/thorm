<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{
    el, text, cls, attrs, concat, read, state,
    on, onMount, repeat, item,
    inc, set, delay, task, push, val
};
use Thorm\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$count = state(0);
$status = state('idle');
$log = state([]);

$app = el('div', [attrs(['class' => 'container p-3'])], [
    el('h1', [], [ text('Task action (CSR)') ]),
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
    ])
]);

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__ . '/../../public/tests/' . $test . '/';
if (!is_dir($path)) { mkdir($path, 0777, true); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'       => 'Task action (CSR)',
    'containerId' => 'app',
    'template'    => __DIR__ . '/../../assets/index-test.tpl.html',
]);

$html_ok = file_put_contents($path . $res['iruri'], $res['irJson']) !== false;
$page_ok = file_put_contents($path . 'index.html', $res['tpl']) !== false;

echo $html_ok ? green("Wrote JSON data file\n") : red("Could not write JSON file\n");
echo $page_ok ? green("Wrote HTML page\n") : red("Could not write HTML page\n");
echo "\n";
