<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use function Thorm\{
    attrs, cls, concat, el, ev, h1, html, http, on, option, p, read, select,
    set, state, text, val, client, task
};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
\$selectedName = state('');
\$status = state(null);
\$response = state('');

\$app = el('div', [cls('container p-3')], [
    h1([cls('h1 mb-1')], [ text('Select + change event + HTTP POST') ]),
    p([cls('text-dark mb-3')], [
        text('Choose a name from the dropdown. The change event posts it to /api/echo and renders the echoed text.')
    ]),

    select([
        cls('form-select'),
        attrs(['name' => 'name']),
        on('change', task([
            set(\$selectedName, ev('target.value'), true),
            http(
                val('/api/echo'),
                'POST',
                \$response,
                \$status,
                ['Content-Type' => 'application/x-www-form-urlencoded'],
                concat('name=', ev('target.value')),
                'text',
                true
            ),
        ]))
    ], [
        option([attrs(['value' => '', 'selected' => true, 'disabled' => true])], [ text('Choose a name...') ]),
        option([attrs(['value' => 'Ada'])], [ text('Ada') ]),
        option([attrs(['value' => 'Linus'])], [ text('Linus') ]),
        option([attrs(['value' => 'Grace'])], [ text('Grace') ]),
        option([attrs(['value' => 'Margaret'])], [ text('Margaret') ]),
    ]),

    p([cls('mt-3 mb-1')], [ text(concat('Selected name: ', read(\$selectedName))) ]),
    p([cls('mb-1')], [ text(concat('HTTP status: ', read(\$status))) ]),
    el('pre', [cls('bg-light text-dark p-3 rounded-3')], [ text(read(\$response)) ]),
]);

\$app = client(\$app);
", true))]);

$selectedName = state('');
$status = state(null);
$response = state('');

$app = el('div', [cls('container p-3')], [
    el('div', [cls('glass p-3 rounded-3')], [
        h1([cls('h1 mb-1')], [ text('Select + change event + HTTP POST') ]),
        p([cls('text-dark mb-3')], [
            text('Choose a name from the dropdown. The change event posts it to /api/echo and renders the echoed text.')
        ]),

        select([
            cls('form-select'),
            attrs(['name' => 'name']),
            on('change', task([
                set($selectedName, ev('target.value'), true),
                http(
                    val('/api/echo/'),
                    'POST',
                    $response,
                    $status,
                    ['Content-Type' => 'application/x-www-form-urlencoded'],
                    concat('name=', ev('target.value')),
                    'text',
                    true
                ),
            ]))
        ], [
            option([attrs(['value' => '', 'selected' => true, 'disabled' => true])], [ text('Choose a name...') ]),
            option([attrs(['value' => 'Ada'])], [ text('Ada') ]),
            option([attrs(['value' => 'Linus'])], [ text('Linus') ]),
            option([attrs(['value' => 'Grace'])], [ text('Grace') ]),
            option([attrs(['value' => 'Margaret'])], [ text('Margaret') ]),
        ]),

        p([cls('mt-3 mb-1')], [ text(concat('Selected name: ', read($selectedName))) ]),
        p([cls('mb-1')], [ text(concat('HTTP status: ', read($status))) ]),
        el('pre', [cls('bg-light text-dark p-3 rounded-3 mb-0')], [ text(read($response)) ]),
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
        'title'         => 'Select HTTP Echo',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
