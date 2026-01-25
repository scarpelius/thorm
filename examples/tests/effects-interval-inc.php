<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{
    el, text, attrs, cls, concat, read, state, fragment,
    every,         // effect helper
    inc,           // unified helper (Listener|Action). We'll pass asAction=true
    html
};
use Thorm\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

// ─────────────────────────────────────────────────────────────────────────────
// model

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
\$tick = state(0);

\$app = fragment([
    el('div', [attrs(['class' => 'container p-3'])], [
        el('h1', [], [ text('Effect: interval + inc') ]),
        el('p', [], [ text(concat('Tick = ', read(\$tick))) ]),
        el('p', [cls('text-muted')], [ text('Should increment once per second.') ]),
    ]),

    // EFFECT: every 1000ms increment tick
    every(1000, [ inc(\$tick, 1, true) ]), // asAction = true
]);
", true))]);

$tick = state(0);

// ─────────────────────────────────────────────────────────────────────────────
// UI

$app = fragment([
    el('div', [attrs(['class' => 'container p-3'])], [
        el('h1', [], [ text('Effect: interval + inc') ]),
        el('p', [], [ text(concat('Tick = ', read($tick))) ]),
        el('p', [cls('text-muted')], [ text('Should increment once per second.') ]),
        $code
    ]),

    // EFFECT: every 1000ms increment tick
    every(1000, [ inc($tick, 1, true) ]), // asAction = true
]);

// ─────────────────────────────────────────────────────────────────────────────
// render → public/tests/<name>/

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__ . '/../../public/tests/' . $test . '/';
if (!is_dir($path)) { mkdir($path, 0777, true); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'       => 'Effect interval + inc',
    'containerId' => 'app',
    'template'    => __DIR__ . '/../../assets/index-test.tpl.html',
]);

$html_ok = file_put_contents($path . $res['iruri'], $res['irJson']) !== false;
$page_ok = file_put_contents($path . 'index.html', $res['tpl']) !== false;

echo $html_ok ? green("Wrote JSON data file\n") : red("Could not write JSON file\n");
echo $page_ok ? green("Wrote HTML page\n") : red("Could not write HTML page\n");
echo "\n";
