<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{attrs, bind, cls, cmp, cond, concat, div, el, eq, get, h1, h4, html, http, item, li, not, on, p, read, repeat, set, show, state, task, text, ul, val, client};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
\$q = state('');
\$status = state(0);
\$hasSearched = state(false);
\$results = state([]);
\$choices = state([
    ['id' => 1, 'title' => 'Dune - Frank Herbert (1965)'],
    ['id' => 2, 'title' => 'Neuromancer - William Gibson (1984)'],
    ['id' => 3, 'title' => 'The Left Hand of Darkness - Ursula K. Le Guin (1969)'],
    ['id' => 4, 'title' => 'Snow Crash - Neal Stephenson (1992)'],
    ['id' => 5, 'title' => 'The Three-Body Problem - Liu Cixin (2006)'],
    ['id' => 6, 'title' => 'The Expanse: Leviathan Wakes - James S. A. Corey (2011)'],
    ['id' => 7, 'title' => 'Hyperion - Dan Simmons (1989)'],
    ['id' => 8, 'title' => 'Red Mars - Kim Stanley Robinson (1992)'],
    ['id' => 9, 'title' => 'Annihilation - Jeff VanderMeer (2014)'],
    ['id' => 10, 'title' => 'The Forever War - Joe Haldeman (1974)'],
    ['id' => 11, 'title' => 'The Windup Girl - Paolo Bacigalupi (2009)'],
    ['id' => 12, 'title' => 'Solaris - StanisÅ‚aw Lem (1961)'],
]);
\$liTpl = li([], [ text(item('title')) ]);

\$app = el('div', [ cls('container p-3') ], [
    h1([cls('h1 mb-1')], [
        text('Search Box Live'),
    ]),
    p([cls('text-dark mb-0')], [
        text('Bind input to state, fetch results, and render a live list.'),
    ]),
    div([cls('input-group')], [
        el('input', [
            cls('form-control'),
            attrs(['placeholder' => 'Search for a book title...']),
            ...bind(\$q, ['type' => 'text'])
        ]),
        el('button', [
            cls('btn btn-primary'),
            on('click', task([
                set(\$hasSearched, val(true), true),
                http(
                    concat('/api/search/?search=', read(\$q)),
                    'GET',
                    \$results,
                    \$status,
                    null,
                    null,
                    'json',
                    true
                ),
            ]))
        ], [ text('Search') ]),
    ]),

    p([cls('text-muted small mt-3 mb-0')], [
        text(cond(
            read(\$hasSearched),
            cond(
                eq(read(\$status), val(0)),
                val('Searching...'),
                cond(
                    cmp('>=', read(\$status), val(200)),
                    cond(get(read(\$results), 0), val('Results ready.'), val('No matches found.')),
                    val('Search failed. Try again.')
                )
            ),
            val('Enter a search term to see matching titles.')
        )),
    ]),

    h4([cls('h6 mt-4')], [ text('Results') ]),
    show(cmp('>=', read(\$status), val(200)),
        show(get(read(\$results), 0),
            el('ul', [ cls('list-group') ], [
                repeat(read(\$results), item('id'),
                    el('li', [cls('list-group-item')], [text(concat(item('title')))]),
                ),
            ])
        )
    ),
    show(read(\$hasSearched),
        show(cmp('>=', read(\$status), val(200)),
            show(not(get(read(\$results), 0)),
                p([cls('text-muted mb-0')], [
                    text('No titles matched this search. Try a broader keyword.')
                ])
            )
        )
    ),
    show(read(\$hasSearched),
        show(eq(read(\$status), val(0)),
            p([cls('text-muted mb-0')], [
                text('Looking for matches...')
            ])
        )
    ),

    h4([cls('h6 mt-4')], [ text('Sample searches') ]),
    ul([], [
        repeat(
            read(\$choices),
            item('id'),
            \$liTpl
        ),
    ]),
]);
", true))]);

$q = state('');
$status = state(0);
$hasSearched = state(false);
$results = state([]);
$choices = state([
    ['id' => 1, 'title' => 'Dune - Frank Herbert (1965)'],
    ['id' => 2, 'title' => 'Neuromancer - William Gibson (1984)'],
    ['id' => 3, 'title' => 'The Left Hand of Darkness - Ursula K. Le Guin (1969)'],
    ['id' => 4, 'title' => 'Snow Crash - Neal Stephenson (1992)'],
    ['id' => 5, 'title' => 'The Three-Body Problem - Liu Cixin (2006)'],
    ['id' => 6, 'title' => 'The Expanse: Leviathan Wakes - James S. A. Corey (2011)'],
    ['id' => 7, 'title' => 'Hyperion - Dan Simmons (1989)'],
    ['id' => 8, 'title' => 'Red Mars - Kim Stanley Robinson (1992)'],
    ['id' => 9, 'title' => 'Annihilation - Jeff VanderMeer (2014)'],
    ['id' => 10, 'title' => 'The Forever War - Joe Haldeman (1974)'],
    ['id' => 11, 'title' => 'The Windup Girl - Paolo Bacigalupi (2009)'],
    ['id' => 12, 'title' => 'Solaris - StanisÅ‚aw Lem (1961)'],
]);
$liTpl = li([], [ text(item('title')) ]);

$app = el('div', [ cls('container p-3') ], [
    h1([cls('h1 mb-1')], [
        text('Search Box Live'),
    ]),
    p([cls('text-dark mb-0')], [
        text('Bind input to state, fetch results, and render a live list.'),
    ]),
    div([cls('input-group')], [
        el('input', [
            cls('form-control'),
            attrs(['placeholder' => 'Search for a book title...']),
            ...bind($q, ['type' => 'text'])
        ]),
        el('button', [
            cls('btn btn-primary'),
            on('click', task([
                set($hasSearched, val(true), true),
                http(
                    concat('/api/search/?search=', read($q)),
                    'GET',
                    $results,
                    $status,
                    null,
                    null,
                    'json',
                    true
                ),
            ]))
        ], [ text('Search') ]),
    ]),

    p([cls('text-muted small mt-3 mb-0')], [
        text(cond(
            read($hasSearched),
            cond(
                eq(read($status), val(0)),
                val('Searching...'),
                cond(
                    cmp('>=', read($status), val(200)),
                    cond(get(read($results), 0), val('Results ready.'), val('No matches found.')),
                    val('Search failed. Try again.')
                )
            ),
            val('Enter a search term to see matching titles.')
        )),
    ]),

    h4([cls('h6 mt-4')], [ text('Results') ]),
    show(cmp('>=', read($status), val(200)),
        show(get(read($results), 0),
            el('ul', [ cls('list-group') ], [
                repeat(read($results), item('id'),
                    el('li', [cls('list-group-item')], [text(concat(item('title')))]),
                ),
            ])
        )
    ),
    show(read($hasSearched),
        show(cmp('>=', read($status), val(200)),
            show(not(get(read($results), 0)),
                p([cls('text-muted mb-0')], [
                    text('No titles matched this search. Try a broader keyword.')
                ])
            )
        )
    ),
    show(read($hasSearched),
        show(eq(read($status), val(0)),
            p([cls('text-muted mb-0')], [
                text('Looking for matches...')
            ])
        )
    ),
    h4([cls('h6 mt-4')], [ text('Sample searches') ]),
    ul([], [
        repeat(
            read($choices),
            item('id'),
            $liTpl
        ),
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
    'template'      => __DIR__.'/../../assets/index-test.tpl.html',
    'opts'          => [
        'title'         => 'Dune - Frank Herbert (1965)',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
