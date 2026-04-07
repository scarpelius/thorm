<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use function Thorm\{el, text, concat, val, on, cls, attrs, read, state, http, cond, get, bind, button, div, form, h1, html, input, p, client};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = div([cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
\$amount = state(10);
\$status = state(0);
\$out = state('');

\$app = div([
    cls('container'),
], [
    form([
        cls('my-5'),
        on('submit', 
            http(
                val('/api/bid/'),
                'POST',
                \$out,
                \$status,
                ['Content-Type' => 'application/x-www-form-urlencoded'],
                concat('amount=', read(\$amount)),
                'json'
            )
        )
    ], [
        div([ cls('col col-lg-2')], [
            input( 
                [ 
                    cls('form-control shadow col-3'), 
                    attrs(['type'=>'number']), ...bind(\$amount, ['type'=>'number']) 
                ]
            ),
        ] ),
        button([ cls('btn btn-primary mt-3') ], [ text('Place bid') ]),
        p([], [ text(get(read(\$out), 'message')) ]),

        p([], [
            text(
                cond(
                    get(read(\$out), 'ok'),
                    'Thanks!',
                    get(get(read(\$out), 'error'), 'message')
                )
            )
        ])
    ])
]);
", true))]);

$amount = state(10);
$status = state(0);
$out = state('');

$app = div([
    cls('container my-5'),
], [
    div([], [
        h1([cls('h1 mb-1')], [
            text('Bid'),
        ]),
        p([cls('text-dark mb-0')], [
            text('Submit bid amounts over HTTP and render response feedback reactively.'),
        ]),
    ]),
    div([ cls('glass p-3 rounded-2') ], [
        form([
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
            div([ cls('col col-lg-2')], [
                input( 
                    [ 
                        cls('form-control shadow col-3'), 
                        attrs(['type'=>'number']), ...bind($amount, ['type'=>'number']) 
                    ]
                ),
            ] ),
            button([ cls('btn btn-primary mt-3') ], [ text('Place bid') ]),
            p([], [ text(get(read($out), 'message')) ]),

            p([], [
                text(
                    cond(
                        get(read($out), 'ok'),
                        'Thanks!',
                        get(get(read($out), 'error'), 'message')
                    )
                )
            ])
        ])
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
        'title'         => 'Bid',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
