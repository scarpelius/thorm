<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{el, text, concat, val, on, cls, attrs, read, state, http, cond, get, bind};
use Thorm\Renderer;
use Thorm\RenderSsr;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

//$cnt = state(0);

$amount = state(10);
$status = state(0);
$out = state('');

$app = el('div', [
    cls('container'),
], [
    el('h1',[], [ text('Bid example') ]),
    el('form', [
        cls('my-5'),
        on('submit', 
            http(
                val('/api/bid/'),
                'POST',
                $out,
                $status,
                ['Content-Type' => 'application/x-www-form-urlencoded'],
                concat('amount=', read($amount)),
                'json'
            )
        )
    ], [
        el('div', [ cls('col col-lg-2')], [
            el('input', 
                [ 
                    cls('form-control shadow col-3'), 
                    attrs(['type'=>'number']), ...bind($amount, ['type'=>'number']) 
                ]
            ),
        ] ),
        el('button', [ cls('btn btn-primary mt-3') ], [ text('Place bid') ]),
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
    ])
]);
;

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__.'/../../public/tests/'.$test.'/';
if(!is_dir($path)) { mkdir($path); }

$renderer = new Renderer();
$ssr = new RenderSsr($renderer);
$ir = $renderer->toIR($app);
$ssrRes = $ssr->renderIr($ir);

$title = 'Bid';
$containerId = 'app';
$templatePath = __DIR__.'/../../assets/index-test.tpl.html';

// ensure the unicity of the iruri
$callerId = md5(__FILE__);
$iruri = $callerId . '.ir.json';
$irJson = json_encode($ir, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$tpl = file_get_contents($templatePath);
$scope = [
    'title'         => htmlspecialchars($title, ENT_QUOTES),
    'containerId'   => htmlspecialchars($containerId, ENT_QUOTES),
    'iruri'         => $iruri,
    'iruri_dir'     => '',
    'html'          => $ssrRes['html']??'',
];
$tpl = preg_replace_callback('/{\$(\w+)}/', function($matches) use($scope)  {
    return $scope[$matches[1]] ?? '';
}, $tpl);

// Inject SSR HTML into container
//$ssrHtml = $ssrRes['html'];

//$containerOpen = '<div id="' . $containerId . '">';
//$containerWithSsr = '<div id="' . $containerId . '">' . $ssrHtml . '</div>';
//$tpl = str_replace($containerOpen . '</div>', $containerWithSsr, $tpl);

// Inject hydration state before module script
$stateJson = json_encode($ssrRes['state'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$stateScript = "<script>window.__THORM_STATE__ = {$stateJson};</script>";
$tpl = preg_replace('/<script type="module">/', $stateScript . "\n<script type=\"module\">", $tpl, 1);

// Switch mount to hydrate
$tpl = str_replace("import { mount } from '/runtime/index.js';", "import { hydrate } from '/runtime/index.js';", $tpl);
$tpl = str_replace("document.getElementById('app')", "document.getElementById('{$containerId}')", $tpl);
$tpl = str_replace("const x = mount(ir, document.getElementById('{$containerId}'));", "const x = hydrate(ir, document.getElementById('{$containerId}'), window.__THORM_STATE__ || {});", $tpl);

// save the bootstrap data
$html = file_put_contents($path . $iruri, $irJson);
// save the page
$json_data = file_put_contents(
    $path . 'index.html',
    $tpl
);

if($html !== false ) { echo green("Wrote html file\n"); } else { echo red("Bad luck, could not write html file.\n"); }
if($json_data !== false ) { echo green("Wrote JSON data file\n"); } else { echo red("Bad luck, could not write JSON file.\n"); }
echo "\n";
