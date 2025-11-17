<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function PhpJs\{
    el, text, concat, on, inc, set, read, state, cls,
    fragment, slot, prop, component, repeat, item, cond, val, eq,
    show
};
use PhpJs\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

/**
* Demo: Component live reactivity for props & slots.
*
* - Props: title reads from $i (counter). Buttons mutate $i → header updates without remounting.
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
$app = el('div', [ cls('container') ], [
    el('h2', [], [ text('Components: live props & slots') ]),

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
]);

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__.'/../../public/tests/'.$test.'/';

if(!is_dir($path)) { mkdir($path, recursive: true); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'         => 'Components: live props & slots',
    'containerId'   => 'app',
    'template'      => __DIR__.'/../../assets/index-test.tpl.html',
]);

// save the bootstrap data
$html = file_put_contents($path . $res['iruri'], $res['irJson']);
// save the page
$json_data = file_put_contents($path . 'index.html', $res['tpl']);

if($html !== false ) { echo green("Wrote html file\n"); } else { echo red("Bad luck, could not write html file.\n"); }
if($json_data !== false ) { echo green("Wrote JSON data file\n"); } else { echo red("Bad luck, could not write JSON file.\n"); }
echo "\n";
