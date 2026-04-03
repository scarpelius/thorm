<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{
    cls, component, concat, cond, div, el, eq, fragment, html, inc, item, on, prop, read,
    repeat, set, show, slot, state, text, val, client,
};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
/**
* Demo: Component live reactivity for props & slots.
*
* - Props: title reads from \$i (counter). Buttons mutate \$i -> header updates without remounting.
* - Slots: default slot renders a list via repeat() driven by \$mode (0/1). Toggle switches arrays.
*          footer slot visibility toggled by \$showFooter.
*/

// Component template (Card)
\$Card = fragment([
    el('div', [ cls('card my-3') ], [
        el('div', [ cls('card-header') ], [ text(prop('title')) ]),
        el('div', [ cls('card-body') ],  [ slot() ]),
        el('div', [ cls('card-footer') ], [ slot('footer') ]),
    ])
]);

// State
\$i = state(1);                  // drives prop: title = \"Card #i\"
\$mode = state(0);               // drives list variant in default slot
\$showFooter = state(true);      // toggles footer slot visibility

// Two alternative item arrays (Expr via val())
\$itemsA = val([ ['id'=>1,'label'=>'Alpha'], ['id'=>2,'label'=>'Bravo'], ['id'=>3,'label'=>'Charlie'] ]);
\$itemsB = val([ ['id'=>3,'label'=>'Charlie'], ['id'=>1,'label'=>'Alpha'], ['id'=>4,'label'=>'Delta'] ]);

// itemsExpr switches between A/B based on \$mode
\$itemsExpr = cond(eq(read(\$mode), val(0)), \$itemsA, \$itemsB);

\$comp = component(
    \$Card,
    [ 'title' => concat('Card #', read(\$i)) ],
    [
        // default slot (unnamed): list driven by \$mode
        'default' => [el('ul', [ cls('list-group') ], [
            repeat(\$itemsExpr, item('id'),
                el('li', [ cls('list-group-item d-flex justify-content-between align-items-center') ], [
                    text(item('label')),
                    el('span', [ cls('badge bg-secondary') ], [ text(concat('#', item('id'))) ])
                ])
            )
        ])],

        // footer slot (named), visibility toggled
        'footer' => [
            el('div', [ cls('d-flex justify-content-between') ], [
                el('small', [], [ text('Footer (static area)') ]),
                // show/hide via \$showFooter
                el('small', [], [
                    text(concat('Visible: ', cond(eq(read(\$showFooter), val(true)), 'yes', 'no')))
                ]),
            ]),
            // Also demonstrate conditional child
            // (Note: show() is inside slot content; SlotMount reconciles children)
            // When false -> node is removed; when true -> node is created/moved
            show(eq(read(\$showFooter), val(true)),
                el('small', [ cls('text-muted') ], [ text('Toggled extra info in footer') ])
            ),
        ],
    ]
);

// The App
\$app = el('div', [ cls('container') ], [
    el('h1', [], [ text('Components: live props & slots') ]),
    el('p', [], [text('Live props and slots update without remounting the component.')]),
    // Controls
    el('div', [ cls('my-2 btn-group') ], [
        el('button', [ cls('btn btn-primary'), on('click', inc(\$i, 1)) ], [ text('Title++') ]),
        el('button', [ cls('btn btn-secondary'), on('click', set(\$mode, cond(eq(read(\$mode), val(0)), val(1), val(0)))) ], [ text('Toggle Items A/B') ]),
        el('button', [ cls('btn btn-outline-dark'), on('click', set(\$showFooter, cond(eq(read(\$showFooter), val(true)), val(false), val(true)))) ], [ text('Toggle Footer') ]),
    ]),

    // Live debug line
    el('p', [ cls('text-muted') ], [
        text(concat('i=', read(\$i), ' | mode=', read(\$mode), ' | footer=', cond(eq(read(\$showFooter), val(true)), 'on', 'off')))
    ]),

    // Component instance
    \$comp,
]);
", true))]);

/**
* Demo: Component live reactivity for props & slots.
*
* - Props: title reads from $i (counter). Buttons mutate $i -> header updates without remounting.
* - Slots: default slot renders a list via repeat() driven by $mode (0/1). Toggle switches arrays.
*          footer slot visibility toggled by $showFooter.
*/

// Component template (Card)
$Card = fragment([
    el('div', [ cls('card my-3') ], [
        el('div', [ cls('card-header') ], [ text(prop('title')) ]),
        el('div', [ cls('card-body') ],  [ slot() ]),
        el('div', [ cls('card-footer') ], [ slot('footer') ]),
    ])
]);

// State
$i = state(1);                  // drives prop: title = "Card #i"
$mode = state(0);               // drives list variant in default slot
$showFooter = state(true);      // toggles footer slot visibility

// Two alternative item arrays (Expr via val())
$itemsA = val([ ['id'=>1,'label'=>'Alpha'], ['id'=>2,'label'=>'Bravo'], ['id'=>3,'label'=>'Charlie'] ]);
$itemsB = val([ ['id'=>3,'label'=>'Charlie'], ['id'=>1,'label'=>'Alpha'], ['id'=>4,'label'=>'Delta'] ]);

// itemsExpr switches between A/B based on $mode
$itemsExpr = cond(eq(read($mode), val(0)), $itemsA, $itemsB);

$comp = component(
    $Card,
    [ 'title' => concat('Card #', read($i)) ],
    [
        // default slot (unnamed): list driven by $mode
        'default' => [el('ul', [ cls('list-group') ], [
            repeat($itemsExpr, item('id'),
                el('li', [ cls('list-group-item d-flex justify-content-between align-items-center') ], [
                    text(item('label')),
                    el('span', [ cls('badge bg-secondary') ], [ text(concat('#', item('id'))) ])
                ])
            )
        ])],

        // footer slot (named), visibility toggled
        'footer' => [
            el('div', [ cls('d-flex justify-content-between') ], [
                el('small', [], [ text('Footer (static area)') ]),
                // show/hide via $showFooter
                el('small', [], [
                    text(concat('Visible: ', cond(eq(read($showFooter), val(true)), 'yes', 'no')))
                ]),
            ]),
            // Also demonstrate conditional child
            // (Note: show() is inside slot content; SlotMount reconciles children)
            // When false -> node is removed; when true -> node is created/moved
            show(eq(read($showFooter), val(true)),
                el('small', [ cls('text-muted') ], [ text('Toggled extra info in footer') ])
            ),
        ],
    ]
);

// The App
$app = el('div', [ cls('container my-5') ], [
    div([ cls('glass p-3 rounded-2') ], [
        el('h1', [], [ text('Components: live props & slots') ]),
        el('p', [], [text('Live props and slots update without remounting the component.')]),
        // Controls
        el('div', [ cls('my-2 btn-group') ], [
            el('button', [ cls('btn btn-primary'), on('click', inc($i, 1)) ], [ text('Title++') ]),
            el('button', [ cls('btn btn-secondary'), on('click', set($mode, cond(eq(read($mode), val(0)), val(1), val(0)))) ], [ text('Toggle Items A/B') ]),
            el('button', [ cls('btn btn-outline-dark'), on('click', set($showFooter, cond(eq(read($showFooter), val(true)), val(false), val(true)))) ], [ text('Toggle Footer') ]),
        ]),

        // Live debug line
        el('p', [ cls('text-muted') ], [
            text(concat('i=', read($i), ' | mode=', read($mode), ' | footer=', cond(eq(read($showFooter), val(true)), 'on', 'off')))
        ]),

        // Component instance
        $comp,
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
    'template'      => __DIR__.'/../../assets/index.tpl.html',
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
