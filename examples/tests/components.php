<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{el, text, fragment, slot, val, prop, component, cls};
use Thorm\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$Layout = fragment([
    el('header', [], [ slot('header') ]),
    el('main',   [], [ slot() ]),
    el('footer', [], [ slot('footer') ]),
]);

// Optional: a template that also reads a prop, e.g. title
$CardTpl = fragment([
    el('div', [ cls('card') ], [
        el('h2', [], [ text(prop('title')) ]),  // reads ctx.props.title at runtime
        slot(),                                 // default body
    ]),
]);

// Use the component with NAMED slots (including 'default')
$NamedSlotsInstance = component($Layout, [], [
    'header'  => [ el('h1', [], [ text(val('Top')) ]) ],
    'default' => [ el('p',  [], [ text(val('Body')) ]) ],
    'footer'  => [ el('p',  [], [ text(val('Bottom')) ]) ],
]);

// Use the component with ONLY DEFAULT slot (unnamed children form)
// This becomes slots: { "default": [...] } in the IR.
$DefaultOnlyInstance = component($Layout, [], [
    el('p', [], [ text(val('Only default content')) ])
]);

// A component that passes PROPS and default slot content
$CardInstance = component($CardTpl, /* props */ [
    'title' => val('Hello, Props!'),
], [
    el('p', [], [ text(val('This is the card body.')) ]),
]);

// Compose a page IR that uses the three component instances
$app = fragment([
    el('div', [ cls('container') ], [
        $NamedSlotsInstance,
        $DefaultOnlyInstance,
        $CardInstance,
    ]),
]);

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__.'/../../public/tests/'.$test.'/';
if(!is_dir($path)) { mkdir($path); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'         => 'Components',
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
