<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{
  attrs, cls, component, concat, cond, div, el, eq, fragment, html, inc, on, prop, read,
  set, slot, state, style, text, val
    client,
};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
// ---------- State ----------
\$i        = state(0);          // drives label \"Card #i\"
\$isHeavy  = state(false);      // drives fontWeight via style map
\$isPrimary= state(false);       // drives class string: adds/removes \"card-primary\"

/**
 * Component template (Card)
 * - Applies class from prop('class') as string
 * - Applies style from prop('style') as map (shallow diff expected in runtime)
 * - Shows label text to verify prop->text flow
 */
\$CardTpl = fragment([
    el('div', [
        cls(prop('class')),
        style(prop('style')),
        attrs(['id' => 'card']),
    ], [
        el('div', [ cls('card-body') ], [
            el('h3', [ cls('card-title') ], [ text(prop('label')) ]),
            slot() // default slot
        ]),
    ])
]);

\$card = component(\$CardTpl, [
    'label' => concat('Card #', read(\$i)),
    'class' => cond(         // STRING usage
        eq(read(\$isPrimary), val(true)),
        'card bg-warning',
        'card'
    ),
    'style' => cond(           // MAP usage
        eq(read(\$isHeavy), val(true)),
        // heavy
        val(['color' => 'tomato', 'fontWeight' => 700]),
        // light
        val(['color' => 'black', 'fontWeight' => 400])
    ),
], [
    // default slot content
    'default' => [
        el('div', [ cls('row') ], [
            el('p',[cls('my-1')], [text('From the forest the fairies were heard, stepping on leaves of moonlight.')]),
            el('p',[cls('my-1')], [text('The water grew afraid and turned into many mirrors, so it could look back.')]),
            el('p',[cls('my-1')], [text('An old shepherd, with a wire staff and a heavy heart, said he had seen them -- not with his eyes, but with fear.')]),
            el('p',[cls('my-1')], [text('And in that moment his shadow faded, for the fairies do not spare those who understand.')]),
        ])
    ],
]);


// ---------- UI ----------
\$app = el('div', [ cls('container py-3') ], [
    el('h1', [], [ text('Components: class (string) + style (map) - live updates') ]),
    el('p', [], [text('Reactively update class strings and style maps on components.')]),
    // Controls
    el('div', [ cls('my-3 btn-group') ], [
        el('button', [ cls('btn btn-primary'), on('click', inc(\$i, 1)) ], [ text('Label++') ]),
        el('button', [ cls('btn btn-warning'), on('click', set(\$isPrimary, cond(read(\$isPrimary), val(false), val(true)))) ], [ text('Toggle Warning Class') ]),
        el('button', [ cls('btn btn-outline-danger'), on('click', set(\$isHeavy, cond(read(\$isHeavy), val(false), val(true)))) ], [ text('Toggle Heavy Style') ]),
    ]),

    // Demo card
    \$card,
]);
", true))]);

// ---------- State ----------
$i        = state(0);          // drives label "Card #i"
$isHeavy  = state(false);      // drives fontWeight via style map
$isPrimary= state(false);       // drives class string: adds/removes "card-primary"

/**
 * Component template (Card)
 * - Applies class from prop('class') as string
 * - Applies style from prop('style') as map (shallow diff expected in runtime)
 * - Shows label text to verify prop->text flow
 */
$CardTpl = fragment([
    el('div', [
        cls(prop('class')),
        style(prop('style')),
        attrs(['id' => 'card']),
    ], [
        el('div', [ cls('card-body') ], [
            el('h3', [ cls('card-title') ], [ text(prop('label')) ]),
            slot() // default slot
        ]),
    ])
]);

$card = component($CardTpl, [
    'label' => concat('Card #', read($i)),
    'class' => cond(
        eq(read($isPrimary), val(true)),
        'card bg-warning',
        'card'
    ),
    'style' => cond(
        eq(read($isHeavy), val(true)),
        val(['color' => 'tomato', 'fontWeight' => 700]),
        val(['color' => 'black', 'fontWeight' => 400])
    ),
], [
    'default' => [
        el('div', [ cls('row') ], [
            el('p',[cls('my-1')], [text('From the forest the fairies were heard, stepping on leaves of moonlight.')]),
            el('p',[cls('my-1')], [text('The water grew afraid and turned into many mirrors, so it could look back.')]),
            el('p',[cls('my-1')], [text('An old shepherd, with a wire staff and a heavy heart, said he had seen them -- not with his eyes, but with fear.')]),
            el('p',[cls('my-1')], [text('And in that moment his shadow faded, for the fairies do not spare those who understand.')]),
        ])
    ],
]);

// ---------- UI ----------
$app = el('div', [ cls('container my-5') ], [
    div([ cls('glass p-3 rounded-2') ], [
        el('h1', [], [ text('Components: class (string) + style (map) - live updates') ]),
        el('p', [], [text('Reactively update class strings and style maps on components.')]),
        // Controls
        el('div', [ cls('my-3 btn-group') ], [
            el('button', [ cls('btn btn-primary'), on('click', inc($i, 1)) ], [ text('Label++') ]),
            el('button', [ cls('btn btn-warning'), on('click', set($isPrimary, cond(read($isPrimary), val(false), val(true)))) ], [ text('Toggle Warning Class') ]),
            el('button', [ cls('btn btn-outline-danger'), on('click', set($isHeavy, cond(read($isHeavy), val(false), val(true)))) ], [ text('Toggle Heavy Style') ]),
        ]),

        // Demo card
        $card,
    ]),
    $code,
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
        'title'         => 'Components: class + style live updates',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
