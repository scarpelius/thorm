<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{
    cls, component, concat, cond, div, el, eq, fragment, html, inc, item, on, prop, read,
    repeat, set, show, slot, state, text, val
    client,
};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
/**
 * Component template (Card)
 * - Header uses prop('title')
 * - Default slot in body
 * - Named 'footer' slot
 */
\$Card = fragment([
    el('div', [ cls('card my-3') ], [
        el('div', [ cls('card-header') ], [ text(prop('title')) ]),
        el('div', [ cls('card-body') ],  [ slot() ]),
        el('div', [ cls('card-footer') ], [ slot('footer') ]),
        el('div', [ cls('card-footer') ], [ slot('footer2') ]),
    ])
]);

// ---------- State ----------
\$i          = state(1);         // drives prop: title = \"Card #i\"
\$mode       = state(0);         // drives A/B items
\$useEmpty   = state(false);     // toggles Default Slot content (List <-> Empty state)
\$footerVar  = state(0);         // toggles Footer content variant (0/1)
\$useAlt     = state(false);     // Demo state to swap the *Expr object* behind the same prop name.
\$showFooter = state(false);

// Two alternative item arrays
\$itemsA = val([ ['id'=>1,'label'=>'Alpha'], ['id'=>2,'label'=>'Bravo'], ['id'=>3,'label'=>'Charlie'] ]);
\$itemsB = val([ ['id'=>3,'label'=>'Charlie'], ['id'=>1,'label'=>'Alpha'], ['id'=>4,'label'=>'Delta'] ]);

// Two distinct Expr objects for the same concept, so identity differs:
\$titleExprA = concat('Card #', read(\$i));
\$titleExprB = concat('Card #', concat(read(\$i), val(' (alt)')));

// A small toggle button to swap expression identity
\$toggle = el('button', [
    cls('btn btn-info'),
    on('click', set(\$useAlt, eq(read(\$useAlt), val(false)))),
], [
    text( 'Toggle title expr')
]);

// Parent controls to provide/remove the footer slot:
\$footerToggle = el('button', [
    on('click', set(
        \$showFooter,
        // invert boolean via cond() -> Expr
        cond(read(\$showFooter), val(false), val(true))
    )),
  cls('btn btn-secondary'),
], [
    text(cond(read(\$showFooter), val('Hide footer slot'), val('Show footer slot')))
]);

// Projected footer content: conditionally render a button Node
\$footerProjected = show(
  read(\$showFooter),                      // Expr|bool
  el('button', [
    cls('btn btn-success')
  ], [
    text('Buy now'),
  ])
);

// itemsExpr switches between A/B
\$itemsExpr = cond(
  eq(read(\$mode), val(0)),
  \$itemsA,
  \$itemsB
);

\$content_list = el('ul', [
    cls('list-group')
], [
    repeat(
        \$itemsExpr,
        item('id'),
        el('li', [ cls('list-group-item d-flex justify-content-between align-items-center') ], [
            text(item('label')),
            el('span', [ cls('badge bg-secondary') ], [ text(concat('#', item('id'))) ])
        ])
    )
]);

\$content_empty = fragment([
    el('div', [
        cls('text-center p-4')
    ], [
        el('div', [ cls('display-6 mb-2') ], [ text('No items') ]),
        el('p', [ cls('text-muted') ], [ text('Toggle back to show the list.') ])
    ])
]);

\$footer_v0 = el('div', [
    cls('d-flex justify-content-between')
], [
    el('small', [], [ text('Footer v0 - static info') ]),
    el('small', [ cls('text-muted') ], [ text('Tip: Try toggling variants.') ]),
]);

\$footer_v1 = el('div', [
    cls('d-flex justify-content-between')
], [
    el('small', [], [ text('Footer v1 - different layout') ]),
    el('span',  [ cls('badge bg-info') ], [ text('LIVE') ]),
]);

// ---------- UI ----------
\$app = el('div', [ cls('container') ], [
    el('h1', [], [ text('Components: live props & slots') ]),
    el('p', [], [text('Advanced slot swapping, conditional content, and reactive props.')]),
    // Controls
    el('div', [ cls('my-2 btn-group') ], [
        el('button', [ cls('btn btn-primary'), on('click', inc(\$i, 1)) ], [ text('Title++') ]),
        el('button', [ cls('btn btn-secondary'), on('click', set(\$mode, cond(eq(read(\$mode), val(0)), val(1), val(0)))) ], [ text('Toggle Items A/B') ]),
        el('button', [ cls('btn btn-outline-warning'), on('click', set(\$useEmpty, cond(eq(read(\$useEmpty), val(true)), val(false), val(true)))) ], [ text('Toggle Default Slot: List/Empty') ]),
        el('button', [ cls('btn btn-outline-info'), on('click', set(\$footerVar, cond(eq(read(\$footerVar), val(0)), val(1), val(0)))) ], [ text('Toggle Footer Variant') ]),
        \$toggle,
        \$footerToggle
    ]),

    // Live debug line
    el('p', [ cls('text-muted') ], [
        text(concat(
            'i=', read(\$i),
            ' | mode=', read(\$mode),
            ' | default=', cond(eq(read(\$useEmpty), val(true)), 'empty', 'list'),
            ' | footerVar=', read(\$footerVar)
        ))
    ]),

    // Component instance
    component(
        \$Card,
        [ 'title' => cond(read(\$useAlt), \$titleExprB, \$titleExprA) ],
        [
            // -------- Default Slot --------
            // We *structurally* switch between two different content shapes:
            // - A <ul> with a repeat() list
            // - An \"empty state\" fragment
            //
            // Note: show(...) nodes are literal children of the default slot.
            // They add/remove DOM under the slot when toggled.
            'default' => [
                show(eq(read(\$useEmpty), val(false)), \$content_list),
                show(eq(read(\$useEmpty), val(true)), \$content_empty)
            ],

            // -------- Named Slot: 'footer' --------
            // Swap content variant 0/1; this demonstrates child changes under a named slot.
            'footer' => [
                show(eq(read(\$footerVar), val(0)), \$footer_v0),
                show(eq(read(\$footerVar), val(1)), \$footer_v1),
            ],
            'footer2' => [\$footerProjected],
        ]
    ),
]);
", true))]);

/**
 * Component template (Card)
 * - Header uses prop('title')
 * - Default slot in body
 * - Named 'footer' slot
 */
$Card = fragment([
    el('div', [ cls('card my-3') ], [
        el('div', [ cls('card-header') ], [ text(prop('title')) ]),
        el('div', [ cls('card-body') ],  [ slot() ]),
        el('div', [ cls('card-footer') ], [ slot('footer') ]),
        el('div', [ cls('card-footer') ], [ slot('footer2') ]),
    ])
]);

// ---------- State ----------
$i          = state(1);         // drives prop: title = "Card #i"
$mode       = state(0);         // drives A/B items
$useEmpty   = state(false);     // toggles Default Slot content (List <-> Empty state)
$footerVar  = state(0);         // toggles Footer content variant (0/1)
$useAlt     = state(false);     // Demo state to swap the *Expr object* behind the same prop name.
$showFooter = state(false);

// Two alternative item arrays
$itemsA = val([ ['id'=>1,'label'=>'Alpha'], ['id'=>2,'label'=>'Bravo'], ['id'=>3,'label'=>'Charlie'] ]);
$itemsB = val([ ['id'=>3,'label'=>'Charlie'], ['id'=>1,'label'=>'Alpha'], ['id'=>4,'label'=>'Delta'] ]);

// Two distinct Expr objects for the same concept, so identity differs:
$titleExprA = concat('Card #', read($i));
$titleExprB = concat('Card #', concat(read($i), val(' (alt)')));

// A small toggle button to swap expression identity
$toggle = el('button', [
    cls('btn btn-info'),
    on('click', set($useAlt, eq(read($useAlt), val(false)))),
], [
    text( 'Toggle title expr')
]);

// Parent controls to provide/remove the footer slot:
$footerToggle = el('button', [
    on('click', set(
        $showFooter,
        // invert boolean via cond() -> Expr
        cond(read($showFooter), val(false), val(true))
    )),
  cls('btn btn-secondary'),
], [
    text(cond(read($showFooter), val('Hide footer slot'), val('Show footer slot')))
]);

// Projected footer content: conditionally render a button Node
$footerProjected = show(
  read($showFooter),                      // Expr|bool
  el('button', [
    cls('btn btn-success')
  ], [
    text('Buy now'),
  ])
);

// itemsExpr switches between A/B
$itemsExpr = cond(
  eq(read($mode), val(0)),
  $itemsA,
  $itemsB
);

$content_list = el('ul', [
    cls('list-group')
], [
    repeat(
        $itemsExpr,
        item('id'),
        el('li', [ cls('list-group-item d-flex justify-content-between align-items-center') ], [
            text(item('label')),
            el('span', [ cls('badge bg-secondary') ], [ text(concat('#', item('id'))) ])
        ])
    )
]);

$content_empty = fragment([
    el('div', [
        cls('text-center p-4')
    ], [
        el('div', [ cls('display-6 mb-2') ], [ text('No items') ]),
        el('p', [ cls('text-muted') ], [ text('Toggle back to show the list.') ])
    ])
]);

$footer_v0 = el('div', [
    cls('d-flex justify-content-between')
], [
    el('small', [], [ text('Footer v0 - static info') ]),
    el('small', [ cls('text-muted') ], [ text('Tip: Try toggling variants.') ]),
]);

$footer_v1 = el('div', [
    cls('d-flex justify-content-between')
], [
    el('small', [], [ text('Footer v1 - different layout') ]),
    el('span',  [ cls('badge bg-info') ], [ text('LIVE') ]),
]);

// ---------- UI ----------
$app = el('div', [ cls('container my-5') ], [
    div([ cls('glass p-3 rounded-2') ], [
        el('h1', [], [ text('Components: live props & slots') ]),
        el('p', [], [text('Advanced slot swapping, conditional content, and reactive props.')]),
        // Controls
        el('div', [ cls('my-2 btn-group') ], [
            el('button', [ cls('btn btn-primary'), on('click', inc($i, 1)) ], [ text('Title++') ]),
            el('button', [ cls('btn btn-secondary'), on('click', set($mode, cond(eq(read($mode), val(0)), val(1), val(0)))) ], [ text('Toggle Items A/B') ]),
            el('button', [ cls('btn btn-outline-warning'), on('click', set($useEmpty, cond(eq(read($useEmpty), val(true)), val(false), val(true)))) ], [ text('Toggle Default Slot: List/Empty') ]),
            el('button', [ cls('btn btn-outline-info'), on('click', set($footerVar, cond(eq(read($footerVar), val(0)), val(1), val(0)))) ], [ text('Toggle Footer Variant') ]),
            $toggle,
            $footerToggle
        ]),

        // Live debug line
        el('p', [ cls('text-muted') ], [
            text(concat(
                'i=', read($i),
                ' | mode=', read($mode),
                ' | default=', cond(eq(read($useEmpty), val(true)), 'empty', 'list'),
                ' | footerVar=', read($footerVar)
            ))
        ]),

        // Component instance
        component(
            $Card,
            [ 'title' => cond(read($useAlt), $titleExprB, $titleExprA) ],
            [
                // -------- Default Slot --------
                // We *structurally* switch between two different content shapes:
                // - A <ul> with a repeat() list
                // - An "empty state" fragment
                //
                // Note: show(...) nodes are literal children of the default slot.
                // They add/remove DOM under the slot when toggled.
                'default' => [
                    show(eq(read($useEmpty), val(false)), $content_list),
                    show(eq(read($useEmpty), val(true)), $content_empty)
                ],

                // -------- Named Slot: 'footer' --------
                // Swap content variant 0/1; this demonstrates child changes under a named slot.
                'footer' => [
                    show(eq(read($footerVar), val(0)), $footer_v0),
                    show(eq(read($footerVar), val(1)), $footer_v1),
                ],
                'footer2' => [$footerProjected],
            ]
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
        'title'         => 'Components: live props & slots',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
