<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use function Thorm\{el, text, concat, cls, attrs, fragment, repeat, read, item, state, cond, eq, val, client};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$base = '/';


$menu_items = state([
    ['id'=> 'home-menu-item', 'link' => '/', 'text' => 'Home'],
    ['id'=> 'povesti-menu-item', 'link' => '/pove%C8%99ti', 'text' => 'Povești'],
    ['id'=> 'steaua_polara-menu-item', 'link' => 'steaua_polara', 'text' => 'Cenaclu'],
    ['id'=> 'contact-menu-item', 'link' => '/contact', 'text' => 'Contact'],
]);

$menu_item = el('li', [
    cls('nav-item mx-2'),
    attrs(['style' => 'max-width:64px;']),
], [
    el('a', [
        cls(concat(
            cond(
                eq(item('link'), val('/')),
                'text-primary ',
                ''
            ),
            'd-flex align-items-center flex-column'
        )),
        attrs([ 'href' => item('link'), 'id' => item('id') ]),
    ], [
        el('div', [cls('menu-text fs-5'), ['style:line-height:30px']], [text(item('text'))]),
    ]),
]);

$header = el('div', [cls('container')], [
    el('h1', [], [ text('Home') ]),
    el('ul', [ cls('nav') ], [
        repeat(
            read($menu_items),
            item('id'),
            $menu_item
        ),
    ]),
]);


$paragraphs = state([
    ['id' => 1, 'text' => 'Și a fost seară peste dealuri, și pământul a suspinat ușor, ca o gură care nu mai știe rugăciune. Atunci ieșiră din văi cuvintele bătrâne, cele ce dorm sub pietre, și se așezară la sfat cu ploaia. „Cine mai ține minte începutul?”, întrebă norul, și tăcerea îi răspunse: „Cel care nu s-a născut încă.” Și așa s-a știut că timpul nu curge, ci se învârte ca o roată fără spițe, sub un soare orb.'],
    ['id' => 2, 'text' => 'Din codru s-au auzit ielele, bătând pasul pe frunze de lună. Apa s-a speriat și s-a făcut oglinzi multe, ca să poată privi înapoi. Un păstor bătrân, cu toiagul de sârmă și inima grea, a zis că le-a văzut — nu cu ochii, ci cu frica. Și în clipa aceea i s-a stins umbra, căci ielele nu suferă pe cei care înțeleg.'],
    ['id' => 3, 'text' => 'Apoi veni vântul dinspre miazănoapte, purtând solie de la moroii din cimitirul vechi. Ziceau că lumea s-a tocit la margini, că sufletele ies prea ușor din trupuri și se pierd pe drum. Preoții cei tineri scriau în cărți fără litere, sperând că Domnul le va umple paginile cu sens. Dar cerul, leneș și neînduplecat, doar clipea din când în când — ca un ochi care visează altă lume.'],
    ['id' => 4, 'text' => 'Și în cele din urmă, din tăcere s-a născut iar satul. Casele au crescut din țărână ca niște rugăciuni cu acoperiș. Copiii au învățat să râdă de umbre, iar femeile au pus busuioc în inimile bărbaților. Iar bătrânii, cu ochii plini de amintiri care nu s-au întâmplat, spuneau încet: „A fost odată o lume fără început. Și poate că încă este.”'],
]);
$paragraph = el('p', [], [
    text(item('text')),
]);

$content = el('div', [
    cls('container content'),
    attrs(['id' => '1337']),
], [
    el('p', [], [ text('Paragraphs:') ]),
    repeat(read($paragraphs), item('id'), $paragraph),
]);

$app = fragment([
    $header,
    $content
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
