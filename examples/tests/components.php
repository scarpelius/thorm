<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{component, cls, div, el, fragment, h1, html, prop, slot, text, val};
use Thorm\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
\$Layout = fragment([
    el('header', [cls('bg-info-subtle mt-3')], [ slot('header') ]),
    el('main',   [cls('bg-info-subtle')], [ slot() ]),
    el('footer', [cls('bg-info-subtle mb-3')], [ slot('footer') ]),
]);

// Optional: a template that also reads a prop, e.g. title
\$CardTpl = fragment([
    el('div', [ cls('card') ], [
        el('h2', [], [ text(prop('title')) ]),  // reads ctx.props.title at runtime
        slot(),                                 // default body
    ]),
]);

// Use the component with NAMED slots (including 'default')
\$NamedSlotsInstance = component(\$Layout, [], [
    'header'  => [ el('p', [cls('p-3 m-0')], [ text(val('Top')) ]) ],
    'default' => [ el('p', [cls('p-3 m-0')], [ text(val('Body')) ]) ],
    'footer'  => [ el('p', [cls('p-3 m-0')], [ text(val('Bottom')) ]) ],
]);

// Use the component with ONLY DEFAULT slot (unnamed children form)
// This becomes slots: { \"default\": [...] } in the IR.
\$DefaultOnlyInstance = component(\$Layout, [], [
    el('p', [], [ text(val('Only default content')) ])
]);

// A component that passes PROPS and default slot content
\$CardInstance = component(\$CardTpl, /* props */ [
    'title' => val('Hello, Props!'),
], [
    el('p', [], [ text(val('This is the card body.')) ]),
]);

// Compose a page IR that uses the three component instances
\$app = fragment([
    el('div', [ cls('container') ], [
        el('div', [], [
            el('h1', [cls('h1 mb-1')], [
                text('Components'),
            ]),
            el('p', [cls('text-dark mb-0')], [
                text('Reusable templates with named slots and props.'),
            ]),
        ]),
        \$NamedSlotsInstance,
        \$DefaultOnlyInstance,
        \$CardInstance,
    ]),
]);
", true))]);

$Layout = fragment([
    el('header', [cls('bg-info-subtle mt-3')], [ slot('header') ]),
    el('main',   [cls('bg-info-subtle')], [ slot() ]),
    el('footer', [cls('bg-info-subtle mb-3')], [ slot('footer') ]),
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
    'header'  => [ el('p', [cls('p-3 m-0')], [ text(val('Top section')) ]) ],
    'default' => [ el('p', [cls('p-3 m-0')], [ text(val('Body section')) ]) ],
    'footer'  => [ el('p', [cls('p-3 m-0')], [ text(val('Bottom section')) ]) ],
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
    el('div', [ cls('container my-5') ], [
        div([ cls('glass p-3 rounded-2') ], [
            el('div', [], [
                el('h1', [cls('h1 mb-1')], [
                    text('Components'),
                ]),
                el('p', [cls('text-dark mb-0')], [
                    text('Reusable templates with named slots and props.'),
                ]),
            ]),
            $NamedSlotsInstance,
            $DefaultOnlyInstance,
            $CardInstance,
        ]),
        $code
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
