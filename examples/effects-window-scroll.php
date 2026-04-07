<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use function Thorm\{
    el, text, attrs, cls, concat, read, state, fragment, style,
    onWindow, set, ev, thorm,
    html,
    client,
};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
\$scrollY = state(0);
\$docH    = state(1);
\$viewH   = state(1);
\$percent = thorm(['round', [
    'cond',
    ['gt', ['sub', read(\$docH), read(\$viewH)], 0],
    ['mul', ['div', read(\$scrollY), ['sub', read(\$docH), read(\$viewH)]], 100],
    0
]]);

\$app = fragment([
    el('div', [attrs(['class' => 'container p-3'])], [
        el('div', [style([
            'position' => 'sticky',
            'top' => '0',
            'background' => '#fff',
            'zIndex' => 1,
            'padding' => '12px 0'
        ])], [
            el('h1', [], [ text('Effect: window scroll') ]),
            el('p', [], [ text(concat('ScrollY: ', read(\$scrollY), 'px')) ]),
            el('p', [], [ text(concat('Scroll: ', \$percent, '%')) ]),
            el('div', [
                cls('progress'),
                style([
                    'width' => '100%',
                    '--scroll' => concat(read(\$scrollY), 'px'),
                    '--doc' => concat(read(\$docH), 'px'),
                    '--view' => concat(read(\$viewH), 'px')
                ])
            ], [
                el('div', [
                    cls('progress-bar'),
                    style([
                        'width' => 'calc((var(--scroll) / (var(--doc) - var(--view))) * 100%)'
                    ])
                ], [])
            ]),
            el('p', [cls('text-muted mt-2')], [
                text('The bar reaches 100% at the bottom of the page.')
            ]),
        ]),
        el('div', [attrs(['style' => 'height: 1200px;'])], []),
    ]),

    // EFFECT: listen to window scroll and update scrollY
    onWindow('scroll', [
        set(\$scrollY, ev('currentTarget.scrollY'), true),
        set(\$docH, ev('currentTarget.document.documentElement.scrollHeight'), true),
        set(\$viewH, ev('currentTarget.innerHeight'), true)
    ])
]);
", true))]);

$scrollY = state(0);
$docH    = state(1);
$viewH   = state(1);
$percent = thorm(['round', [
    'cond',
    ['gt', ['sub', read($docH), read($viewH)], 0],
    ['mul', ['div', read($scrollY), ['sub', read($docH), read($viewH)]], 100],
    0
]]);

$app = fragment([
    el('div', [attrs(['class' => 'container p-3'])], [
        el('div', [style([
            'position' => 'sticky',
            'top' => '0',
            'background' => '#fff',
            'zIndex' => 1,
            'padding' => '12px 0'
        ])], [
            el('h1', [], [ text('Effect: window scroll') ]),
            el('p', [], [ text(concat('ScrollY: ', read($scrollY), 'px')) ]),
            el('p', [], [ text(concat('Scroll: ', $percent, '%')) ]),
            el('div', [
                cls('progress'),
                style([
                    'width' => '100%',
                    '--scroll' => concat(read($scrollY), 'px'),
                    '--doc' => concat(read($docH), 'px'),
                    '--view' => concat(read($viewH), 'px')
                ])
            ], [
                el('div', [
                    cls('progress-bar'),
                    style([
                        'width' => 'calc((var(--scroll) / (var(--doc) - var(--view))) * 100%)'
                    ])
                ], [])
            ]),
            el('p', [cls('text-muted mt-2')], [
                text('The bar reaches 100% at the bottom of the page.')
            ]),
        ]),
        el('div', [attrs(['style' => 'height: 1200px;'])], [
            $code
        ]),
    ]),

    // EFFECT: listen to window scroll and update scrollY
    onWindow('scroll', [
        set($scrollY, ev('currentTarget.scrollY'), true),
        set($docH, ev('currentTarget.document.documentElement.scrollHeight'), true),
        set($viewH, ev('currentTarget.innerHeight'), true)
    ]),
]);


$app = client(el('div', [], [$app]));

$renderer = new Render();
$res = $renderer->render($app);

$build = BuildExample::build([
    'name'          => strtolower(pathinfo(__FILE__, PATHINFO_FILENAME)),
    'path'          => __DIR__.'/../public/examples/',
    'renderer'      => $res,
    'template'      => __DIR__.'/../assets/index.tpl.html',
    'opts'          => [
        'title'         => 'Effect: window scroll',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
