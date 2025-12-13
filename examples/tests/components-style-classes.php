<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{
  el, text, concat, on, inc, set, read, state, cls,eq,
  fragment, slot, prop, component, repeat, item, cond, val, show, style, attrs
};
use Thorm\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }


// ---------- State ----------
$i        = state(0);          // drives label "Card #i"
$isHeavy  = state(false);      // drives fontWeight via style map
$isPrimary= state(false);       // drives class string: adds/removes "card-primary"

/**
 * Component template (Card)
 * - Applies class from prop('class') as string
 * - Applies style from prop('style') as map (shallow diff expected in runtime)
 * - Shows label text to verify prop→text flow
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
    'class' => cond(         // STRING usage
        eq(read($isPrimary), val(true)),
        'card bg-warning',
        'card'
    ),
    'style' => cond(           // MAP usage
        eq(read($isHeavy), val(true)),
        // heavy
        val(['color' => 'tomato', 'fontWeight' => 700]),
        // light
        val(['color' => 'black', 'fontWeight' => 400])
    ),
], [
    // default slot content
    'default' => [
        el('div', [ cls('row') ], [ 
            el('p',[cls('my-1')], [text('Din codru s-au auzit ielele, bătând pasul pe frunze de lună.')]),
            el('p',[cls('my-1')], [text('Apa s-a speriat și s-a făcut oglinzi multe, ca să poată privi înapoi.')]),
            el('p',[cls('my-1')], [text('Un păstor bătrân, cu toiagul de sârmă și inima grea, a zis că le-a văzut — nu cu ochii, ci cu frica.')]),
            el('p',[cls('my-1')], [text('Și în clipa aceea i s-a stins umbra, căci ielele nu suferă pe cei care înțeleg.')]),
        ])
    ],
]);


// ---------- UI ----------
$app = el('div', [ cls('container py-3') ], [
    el('h2', [], [ text('Components: class (string) + style (map) — live updates') ]),

    // Controls
    el('div', [ cls('my-3 btn-group') ], [
        el('button', [ cls('btn btn-primary'), on('click', inc($i, 1)) ], [ text('Label++') ]),
        el('button', [ cls('btn btn-warning'), on('click', set($isPrimary, cond(read($isPrimary), val(false), val(true)))) ], [ text('Toggle Warning Class') ]),
        el('button', [ cls('btn btn-outline-danger'), on('click', set($isHeavy, cond(read($isHeavy), val(false), val(true)))) ], [ text('Toggle Heavy Style') ]),
    ]),

    // Demo card
    $card,
]);

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__.'/../../public/tests/'.$test.'/';
if(!is_dir($path)) { mkdir($path); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'         => 'Components: class + style live updates',
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
