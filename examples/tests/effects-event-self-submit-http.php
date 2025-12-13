<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{
    el, text, attrs, cls, concat, delay, read, val, num, ev, state, fragment,
    selectorTarget,
    onSelf,                  
    set, http, navigate, on, redirect
};
use Thorm\Renderer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }
$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));

// ─────────────────────────────────────────────────────────────────────────────
// model

$name    = state('');
$status  = state(null);
$resp    = state(null);
$reqHdrs = state('');   // request headers
$hdrsTxt = state('');   // pretty headers (optional; uses stringify if you added it)

// ─────────────────────────────────────────────────────────────────────────────
// UI

$form = el('form', 
    [
        attrs([
            'class'     => 'd-flex gap-2', 
            'id'        => 'frm',
            'method'    => 'POST',
        ]),
    ], 
    [
    el('input', [
        attrs(['type' => 'text', 'name' => 'name', 'placeholder' => 'Your name', 'class' => 'form-control']),
        // keep atom in sync while typing (props listener)
        on('input', set($name, ev('target.value')))
    ]),
    el('button', [cls('btn btn-primary'), attrs(['type' => 'submit']) ], [ text('Send') ])
]);

$app = fragment([
    el('div', [attrs(['class' => 'container p-3'])], [
        el('h1', [], [ text('Effect: onSelf(submit) + HTTP POST + navigate') ]),
        $form,
        el('p', [cls('mt-3')], [ text(concat('Name = ', read($name))) ]),
        el('p', [], [ text(concat('Status = ', read($status))) ]),
        el('p', [], [ text('Response:') ]),
        el('pre', [cls('bg-light p-2')], [ text(read($resp)) ]),
        el('p', [], [ text('Request Headers:') ]),
        el('pre', [cls('bg-light p-2')], [text(read($reqHdrs))]),
        el('p', [], [ text('Response Headers:') ]),
        el('pre', [cls('bg-light p-2')], [ text(read($hdrsTxt)) ]),
        el('p', [cls('text-muted')], [ text('Submitting should POST, show status/body/headers, then redirect.') ]),
    ]),

    // EFFECT: bind to this form's submit (use target selector)
    onSelf('submit', [
        // POST body (simple x-www-form-urlencoded example)
        http(
            val('/api/echo/'),  // url
            'POST',             // method
            $resp,              // to
            $status,            // response status
            [ 'Content-Type' => 'application/x-www-form-urlencoded' ], // request headers
            concat('name=', read($name)), // body
            'text',             // 
            true,               // asAction
            $hdrsTxt            // response headers
        ),
        // after posting, redirect
        delay(1500, [ redirect(val('/tests/'.$test.'/thanks.html'), false) ])
    ], [                                // options array for effect
        'passive'           => false,   // if true will ignore preventDefault
        'preventDefault'    => true,    // stops form to submit and refresh the page
        'stopPropagation'   => true     // stops event propagation
    ], selectorTarget('#frm')) // we select form id by '#frm', it is a JavaScript document.querySelect() requirement
]);

// ─────────────────────────────────────────────────────────────────────────────
// render → public/tests/<name>/

$path = __DIR__ . '/../../public/tests/' . $test . '/';
if (!is_dir($path)) { mkdir($path, 0777, true); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'       => 'Effect onSelf submit + HTTP POST',
    'containerId' => 'app',
    'template'    => __DIR__ . '/../../assets/index-test.tpl.html',
]);

$html_ok = file_put_contents($path . $res['iruri'], $res['irJson']) !== false;
$page_ok = file_put_contents($path . 'index.html', $res['tpl']) !== false;

echo $html_ok ? green("Wrote JSON data file\n") : red("Could not write JSON file\n");
echo $page_ok ? green("Wrote HTML page\n") : red("Could not write HTML page\n");
echo "\n";
