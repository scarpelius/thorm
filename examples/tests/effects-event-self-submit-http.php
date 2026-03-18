<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{
    attrs, cls, concat, delay, el, ev, fragment, html, http, on, onSelf, read, redirect,
    selectorTarget, set, state, text, val, client
};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-5')], [html(highlight_string("<?php
\$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));

// model

\$name    = state('');
\$status  = state(null);
\$resp    = state(null);
\$reqHdrs = state('');   // request headers
\$hdrsTxt = state('');   // pretty headers (optional; uses stringify if you added it)

// UI

\$form = el('form',
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
        on('input', set(\$name, ev('target.value')))
    ]),
    el('button', [cls('btn btn-primary'), attrs(['type' => 'submit']) ], [ text('Send') ])
]);

\$app = fragment([
    el('div', [attrs(['class' => 'container p-3'])], [
        el('h1', [], [ text('Effect: onSelf(submit) + HTTP POST + navigate') ]),
        el('p', [], [text('Submit a form, POST to an API, then redirect on completion.')]),
        \$form,
        el('p', [cls('mt-3')], [ text(concat('Name = ', read(\$name))) ]),
        el('p', [], [ text(concat('Status = ', read(\$status))) ]),
        el('p', [], [ text('Response:') ]),
        el('pre', [cls('bg-light p-2')], [ text(read(\$resp)) ]),
        el('p', [], [ text('Request Headers:') ]),
        el('pre', [cls('bg-light p-2')], [text(read(\$reqHdrs))]),
        el('p', [], [ text('Response Headers:') ]),
        el('pre', [cls('bg-light p-2')], [ text(read(\$hdrsTxt)) ]),
        el('p', [cls('text-muted')], [ text('Submitting should POST, show status/body/headers, then redirect.') ]),
    ]),

    // EFFECT: bind to this form's submit (use target selector)
    onSelf('submit', [
        // POST body (simple x-www-form-urlencoded example)
        http(
            val('/api/echo/'),  // url
            'POST',             // method
            \$resp,              // to
            \$status,            // response status
            [ 'Content-Type' => 'application/x-www-form-urlencoded' ], // request headers
            concat('name=', read(\$name)), // body
            'text',             //
            true,               // asAction
            \$hdrsTxt            // response headers
        ),
        // after posting, redirect
        delay(1500, [ redirect(val('/tests/'.\$test.'/thanks.html'), false) ])
    ], [                                // options array for effect
        'passive'           => false,   // if true will ignore preventDefault
        'preventDefault'    => true,    // stops form to submit and refresh the page
        'stopPropagation'   => true     // stops event propagation
    ], selectorTarget('#frm')) // we select form id by '#frm', it is a JavaScript document.querySelect() requirement
]);
", true))]);

$test = 'effects-event-self-submit-http';

// model
$name    = state('');
$status  = state(null);
$resp    = state(null);
$reqHdrs = state('');   // request headers
$hdrsTxt = state('');   // pretty headers (optional; uses stringify if you added it)

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
        el('div', [attrs(['class' => 'glass p-3 rounded-2'])], [
            el('h1', [], [ text('Effect: onSelf(submit) + HTTP POST + navigate') ]),
            el('p', [], [text('Submit a form, POST to an API, then redirect on completion.')]),
            $form,
            el('p', [cls('mt-3')], [ text(concat('Name = ', read($name))) ]),
            el('p', [], [ text(concat('Status = ', read($status))) ]),
            el('p', [], [ text('Response:') ]),
            el('pre', [cls('bg-light p-2 text-dark')], [ text(read($resp)) ]),
            el('p', [], [ text('Request Headers:') ]),
            el('pre', [cls('bg-light p-2 text-dark')], [text(read($reqHdrs))]),
            el('p', [], [ text('Response Headers:') ]),
            el('pre', [cls('bg-light p-2 text-dark')], [ text(read($hdrsTxt)) ]),
            el('p', [cls('text-muted')], [ text('Submitting should POST, show status/body/headers, then redirect.') ]),
        ]),
        $code,
    ]),

    // EFFECT: bind to this form's submit (use target selector)
    onSelf('submit', [
        // POST body (simple x-www-form-urlencoded example)
        http(
            val('/api/echo'),
            'POST',
            $resp,
            $status,
            [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
            concat('name=', read($name)),
            'text',
            true,
            $hdrsTxt
        ),
        // after posting, redirect
        delay(1500, [ redirect(concat('/thanks?name=', read($name)), false) ])
    ], [
        'passive'           => false,
        'preventDefault'    => true,
        'stopPropagation'   => true
    ], selectorTarget('#frm'))
]);

$app = client(el('div', [], [$app]));

$renderer = new Render();
$res = $renderer->render($app);

$build = BuildExample::build([
    'name'          => $test,
    'path'          => __DIR__.'/../../public/tests/',
    'renderer'      => $res,
    'template'      => __DIR__.'/../../assets/index-test.tpl.html',
    'opts'          => [
        'title'         => 'Effect onSelf submit + HTTP POST',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
