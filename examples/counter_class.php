<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Thorm\BuildExample;
use Thorm\IR\Action\Listener;
use Thorm\IR\Atom;
use Thorm\IR\Expr\Expr;
use Thorm\IR\Node\ElNode;
use Thorm\IR\Node\HtmlNode;
use Thorm\IR\Node\ShowNode;
use Thorm\IR\Node\TextNode;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = new ElNode('div', [['cls', 'bg-body-secondary p-3 rounded-4 border mt-5']], [
    new HtmlNode(highlight_string(<<<'PHP'
<?php
$cnt = new Atom(1);

$countExpr = Expr::concat('Count: ', Expr::read($cnt));
$isEven = Expr::op('eq', Expr::op('mod', Expr::read($cnt), 2), 0);
$isOdd = Expr::op('eq', Expr::op('mod', Expr::read($cnt), 2), 1);
$isThirteenMultiple = Expr::op('eq', Expr::op('mod', Expr::read($cnt), 13), 0);

$app = new ElNode('div', [['cls', 'container mt-5']], [
    new ElNode('h2', [['cls', 'h4']], [
        new TextNode($countExpr),
        new ShowNode($isEven, new ElNode('span', [['cls', '']], [new TextNode(' is even')])),
        new ShowNode($isOdd, new ElNode('span', [['cls', '']], [new TextNode(' is odd')])),
        new ShowNode($isThirteenMultiple, new ElNode('p', [['cls', 'h6']], [new TextNode('How odd is that 🙃?')])),
    ]),
    new ElNode('p', [], [
        new TextNode('A simple stateful counter that reacts to clicks and shows even/odd state.'),
    ]),
    new ElNode('button', [
        ['cls', 'btn btn-warning'],
        ['on', 'click', Listener::inc($cnt, 1)],
    ], [new TextNode('Inc')]),
    new ShowNode($isEven, new ElNode('span', [['cls', 'm-5 fs-5']], [new TextNode('Even!')])),
]);

$app->render = ['target' => 'client'];
PHP, true)),
]);

$cnt = new Atom(1);

$countExpr = Expr::concat('Count: ', Expr::read($cnt));
$isEven = Expr::op('eq', Expr::op('mod', Expr::read($cnt), 2), 0);
$isOdd = Expr::op('eq', Expr::op('mod', Expr::read($cnt), 2), 1);
$isThirteenMultiple = Expr::op('eq', Expr::op('mod', Expr::read($cnt), 13), 0);

$app = new ElNode('div', [['cls', 'container mt-5']], [
    new ElNode('h2', [['cls', 'h4']], [
        new TextNode($countExpr),
        new ShowNode($isEven, new ElNode('span', [['cls', '']], [new TextNode(' is even')])),
        new ShowNode($isOdd, new ElNode('span', [['cls', '']], [new TextNode(' is odd')])),
        new ShowNode($isThirteenMultiple, new ElNode('p', [['cls', 'h6']], [new TextNode('How odd is that 🙃?')])),
    ]),
    new ElNode('p', [], [
        new TextNode('A simple stateful counter that reacts to clicks and shows even/odd state.'),
    ]),
    new ElNode('button', [
        ['cls', 'btn btn-warning'],
        ['on', 'click', Listener::inc($cnt, 1)],
    ], [new TextNode('Inc')]),
    new ShowNode($isEven, new ElNode('span', [['cls', 'm-5 fs-5']], [new TextNode('Even!')])),
    $code,
]);

$app->render = ['target' => 'client'];

$renderer = new Render();
$res = $renderer->render($app);

$build = BuildExample::build([
    'name'          => strtolower(pathinfo(__FILE__, PATHINFO_FILENAME)),
    'path'          => __DIR__.'/../public/examples/',
    'renderer'      => $res,
    'template'      => __DIR__.'/../assets/index.tpl.html',
    'opts'          => [
        'title'         => 'Counter Class',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
