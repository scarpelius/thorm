<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{
    el, text, cls, attrs, concat, read, state,
    on, onMount, repeat, item,
    inc, set, delay, task, push, val
};
use Thorm\Renderer;
use Thorm\RenderSsr;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$count = state(0);
$status = state('idle');
$log = state([]);

$app = el('div', [attrs(['class' => 'container p-3'])], [
    el('h1', [], [ text('Task action (SSR)') ]),
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
$ssr = new RenderSsr($renderer);
$ir = $renderer->toIR($app);
$ssrRes = $ssr->renderIr($ir);

$title = 'Task action (SSR)';
$containerId = 'app';
$templatePath = __DIR__ . '/../../assets/index-test-ssr.tpl.html';

$callerId = md5(__FILE__);
$iruri = $callerId . '.ir.json';
$irJson = json_encode($ir, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$tpl = file_get_contents($templatePath);
$scope = [
    'title'       => htmlspecialchars($title, ENT_QUOTES),
    'containerId' => htmlspecialchars($containerId, ENT_QUOTES),
    'iruri'       => $iruri,
    'iruri_dir'   => '',
    'html'        => $ssrRes['html'] ?? '',
    'stateJson'   => json_encode($ssrRes['state'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
];
$tpl = preg_replace_callback('/{\$(\w+)}/', function($matches) use ($scope) {
    return $scope[$matches[1]] ?? '';
}, $tpl);

$html_ok = file_put_contents($path . $iruri, $irJson) !== false;
$page_ok = file_put_contents($path . 'index.html', $tpl) !== false;

echo $html_ok ? green("Wrote JSON data file\n") : red("Could not write JSON file\n");
echo $page_ok ? green("Wrote HTML page\n") : red("Could not write HTML page\n");
echo "\n";
