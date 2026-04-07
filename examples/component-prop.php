<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use function Thorm\{attrs, el, text, fragment, slot, val, prop, component, cls, state, inc, read, on, concat, html, client};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
\$i = state(1);

// Optional: a template that also reads a prop, e.g. title
\$CardTpl = fragment([
    el('div', [ cls('card mt-5') ], [
        el('div', [cls('card-header')], [
            el('h2', [cls('card-title')], [ text(prop('title')) ]), // reads ctx.props.title at runtime
        ]),
        slot(),                                                     // default component slot
    ]),
]);

\$bodySlot = el('div', [cls('card-body')], [
    el('button', [ 
        cls('btn btn-primary col-3 mx-auto'),
        on('click', inc(\$i, 1)),
    ], [ text(val('Inc.')) ]),
    el('p', [cls('my-3')], [ 
        text(val('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et 
        dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea 
        commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla 
        pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est 
        laborum.')),
        el('a', [
            cls('px-3 link-opacity-50 link-underline link-underline-opacity-50'), 
            attrs([
                'href'      => 'https://www.lipsum.com/',
                'target'    => '_blank',
                'rel'       => 'nofollow'
            ]),
        ], [
            text('https://www.lipsum.com/')
        ])
    ]),
]);

// A component that passes PROPS and default slot content
\$CardInstance = component(\$CardTpl, /* props */ [
    'title' => concat('Hello, Props! ', read(\$i)),
], [
    \$bodySlot,
]);

// Compose a page IR that uses the three component instances
\$app = el('div', [ cls('container') ], [
    \$CardInstance,
    el('p', [ 
        cls('text-muted fs-1')
    ], [
        text(concat('i=', read(\$i)))
    ]),
]);
", true))]);


$i = state(1);

// Optional: a template that also reads a prop, e.g. title
$CardTpl = fragment([
    el('div', [ cls('card mt-5') ], [
        el('div', [cls('card-header')], [
            el('h2', [cls('card-title')], [ text(prop('title')) ]),  // reads ctx.props.title at runtime
        ]),
        slot(),                                     // default component slot
    ]),
]);

$bodySlot = el('div', [cls('card-body')], [
    el('button', [ 
        cls('btn btn-primary col-3 mx-auto'),
        on('click', inc($i, 1)),
    ], [ text(val('Inc.')) ]),
    el('p', [cls('my-3')], [ 
        text(val('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.')),
        el('a', [
            cls('px-3 link-opacity-50 link-underline link-underline-opacity-50'), 
            attrs([
                'href'      => 'https://www.lipsum.com/',
                'target'    => '_blank',
                'rel'       => 'nofollow'
            ]),
        ], [
            text('https://www.lipsum.com/')
        ])
    ]),
]);

// A component that passes PROPS and default slot content
$CardInstance = component($CardTpl, /* props */ [
    'title' => concat('Hello, Props! ', read($i)),
], [
    $bodySlot,
]);

// Compose a page IR that uses the three component instances
$app = el('div', [ cls('container') ], [
    $CardInstance,
    el('p', [ 
        cls('text-muted fs-1')
    ], [
        text(concat('i=', read($i)))
    ]),
    $code
]);





$app = client($app);

$renderer = new Render();
$res = $renderer->render($app);

$build = BuildExample::build([
    'name'          => strtolower(pathinfo(__FILE__, PATHINFO_FILENAME)),
    'path'          => __DIR__.'/../public/examples/',
    'renderer'      => $res,
    'template'      => __DIR__.'/../assets/index.tpl.html',
    'opts'          => [
        'title'         => 'Component - Props',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
