<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use function Thorm\{
    attrs, client, cls, concat, cond, div, el, eq, every, fragment, html, item, on, p,
    read, repeat, set, state, style, text, thorm, val
};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

function makeGrid(int $rows, int $cols): array
{
    $grid = [];
    for ($r = 0; $r < $rows; $r++) {
        $row = [];
        for ($c = 0; $c < $cols; $c++) {
            $row[] = 0;
        }
        $grid[] = $row;
    }
    return $grid;
}

function seedGlider(array $grid, int $r, int $c): array
{
    $grid[$r + 0][$c + 1] = 1;
    $grid[$r + 1][$c + 2] = 1;
    $grid[$r + 2][$c + 0] = 1;
    $grid[$r + 2][$c + 1] = 1;
    $grid[$r + 2][$c + 2] = 1;
    return $grid;
}

function nextGen(array $grid): array
{
    $rows = count($grid);
    $cols = count($grid[0] ?? []);
    $out = $grid;

    $count = function(int $r, int $c) use ($grid, $rows, $cols): int {
        $n = 0;
        for ($dr = -1; $dr <= 1; $dr++) {
            for ($dc = -1; $dc <= 1; $dc++) {
                if ($dr === 0 && $dc === 0) {
                    continue;
                }
                $rr = ($r + $dr + $rows) % $rows;
                $cc = ($c + $dc + $cols) % $cols;
                $n += $grid[$rr][$cc] ? 1 : 0;
            }
        }
        return $n;
    };

    for ($r = 0; $r < $rows; $r++) {
        for ($c = 0; $c < $cols; $c++) {
            $alive = $grid[$r][$c] === 1;
            $nbrs = $count($r, $c);
            if ($alive && ($nbrs < 2 || $nbrs > 3)) {
                $out[$r][$c] = 0;
            }
            if (!$alive && $nbrs === 3) {
                $out[$r][$c] = 1;
            }
        }
    }
    return $out;
}

function frameFromGrid(array $grid): array
{
    $frame = [];
    foreach ($grid as $r => $row) {
        $cells = [];
        foreach ($row as $c => $alive) {
            $cells[] = ['id' => $c, 'alive' => $alive ? 1 : 0];
        }
        $frame[] = ['id' => $r, 'cells' => $cells];
    }
    return $frame;
}

$rows = 20;
$cols = 20;
$framesCount = 60;

$grid = makeGrid($rows, $cols);
$grid = seedGlider($grid, 2, 2);

$frames = [];
for ($i = 0; $i < $framesCount; $i++) {
    $frames[] = frameFromGrid($grid);
    $grid = nextGen($grid);
}

$code = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-4')], [html(highlight_string("<?php
\$frameIndex = state(0);
\$speed = state(1);
\$running = state(true);
\$currentFrame = state([]);

// Tick every 200ms, advance by speed (1..10), wrap by frame count.
\$nextIndex = thorm(['mod', ['add', read(\$frameIndex), read(\$speed)], {$framesCount}]);
\$tickIndex = thorm(['cond', read(\$running), \$nextIndex, read(\$frameIndex)]);
\$nextFrame = thorm(['get', read(\$framesAtom), \$tickIndex]);

\$rowTpl = el('div', [style(['display' => 'flex'])], [
    repeat(item('cells'), concat(read(\$frameIndex), '-', item('id')),
        el('div', [
            style([
                'width' => '12px',
                'height' => '12px',
                'border' => '1px solid #222',
                'background' => cond(eq(item('alive'), val(1)), '#00d27a', '#111')
            ])
        ], [])
    ),
]);

\$app = fragment([
    el('div', [attrs(['class' => 'container my-5 p-3'])], [
        client(
            div([ cls('glass p-3 rounded-2') ], [
                el('h1', [], [ text('Conway Game of Life') ]),
                el('p', [cls('text-muted')], [
                    text('A precomputed demo using thorm() for tick math and simple controls.')
                ]),
                el('div', [cls('d-flex gap-2 align-items-center my-3')], [
                    el('button', [
                        cls('btn btn-primary'),
                        on('click', set(\$running, cond(read(\$running), val(false), val(true))))
                    ], [ text(cond(read(\$running), 'Pause', 'Run')) ]),
                    el('button', [
                        cls('btn btn-outline-dark'),
                        on('click', set(\$speed, thorm(['max', ['sub', read(\$speed), 1], 1])))
                    ], [ text('Speed -') ]),
                    el('button', [
                        cls('btn btn-outline-dark'),
                        on('click', set(\$speed, thorm(['min', ['add', read(\$speed), 1], 10])))
                    ], [ text('Speed +') ]),
                    el('span', [cls('text-dark ms-2')], [
                        text(concat('Speed: ', read(\$speed), ' | Frame: ', read(\$frameIndex)))
                    ]),
                ]),
                el('div', [style(['display' => 'inline-block', 'padding' => '8px', 'background' => '#0d0d0d'])], [
                    repeat(read(\$currentFrame), concat(read(\$frameIndex), '-', item('id')), \$rowTpl)
                ]),
            ])
        ),
    ]),
    every(200, [
        set(\$frameIndex, \$tickIndex, true),
        set(\$currentFrame, \$nextFrame, true)
    ]),
]);
", true))]);

$precomputeCode = el('div', [cls('bg-body-secondary p-3 rounded-4 border mt-4')], [html(highlight_string("<?php
// Precompute frames in PHP and render a frame index in the UI.

function makeGrid(int \$rows, int \$cols): array {
    \$grid = [];
    for (\$r = 0; \$r < \$rows; \$r++) {
        \$row = [];
        for (\$c = 0; \$c < \$cols; \$c++) {
            \$row[] = 0;
        }
        \$grid[] = \$row;
    }
    return \$grid;
}

function seedGlider(array \$grid, int \$r, int \$c): array {
    \$grid[\$r + 0][\$c + 1] = 1;
    \$grid[\$r + 1][\$c + 2] = 1;
    \$grid[\$r + 2][\$c + 0] = 1;
    \$grid[\$r + 2][\$c + 1] = 1;
    \$grid[\$r + 2][\$c + 2] = 1;
    return \$grid;
}

function nextGen(array \$grid): array
{
    \$rows = count(\$grid);
    \$cols = count(\$grid[0] ?? []);
    \$out = \$grid;

    \$count = function(int \$r, int \$c) use (\$grid, \$rows, \$cols): int {
        \$n = 0;
        for (\$dr = -1; \$dr <= 1; \$dr++) {
            for (\$dc = -1; \$dc <= 1; \$dc++) {
                if (\$dr === 0 && \$dc === 0) {
                    continue;
                }
                \$rr = (\$r + \$dr + \$rows) % \$rows;
                \$cc = (\$c + \$dc + \$cols) % \$cols;
                \$n += \$grid[\$rr][\$cc] ? 1 : 0;
            }
        }
        return \$n;
    };

    for (\$r = 0; \$r < \$rows; \$r++) {
        for (\$c = 0; \$c < \$cols; \$c++) {
            \$alive = \$grid[\$r][\$c] === 1;
            \$nbrs = \$count(\$r, \$c);
            if (\$alive && (\$nbrs < 2 || \$nbrs > 3)) {
                \$out[\$r][\$c] = 0;
            }
            if (!\$alive && \$nbrs === 3) {
                \$out[\$r][\$c] = 1;
            }
        }
    }
    return \$out;
}

function frameFromGrid(array \$grid): array
{
    \$frame = [];
    foreach (\$grid as \$r => \$row) {
        \$cells = [];
        foreach (\$row as \$c => \$alive) {
            \$cells[] = ['id' => \$c, 'alive' => \$alive ? 1 : 0];
        }
        \$frame[] = ['id' => \$r, 'cells' => \$cells];
    }
    return \$frame;
}

\$rows = 20;
\$cols = 20;
\$framesCount = 60;

\$grid = makeGrid(\$rows, \$cols);
\$grid = seedGlider(\$grid, 2, 2);

\$frames = [];
for (\$i = 0; \$i < \$framesCount; \$i++) {
    \$frames[] = frameFromGrid(\$grid);
    \$grid = nextGen(\$grid);
}
", true))]);

$framesAtom = state($frames);
$frameIndex = state(0);
$speed = state(1);
$running = state(true);
$currentFrame = state($frames[0] ?? []);

$nextIndex = thorm(['mod', ['add', read($frameIndex), read($speed)], $framesCount]);
$tickIndex = thorm(['cond', read($running), $nextIndex, read($frameIndex)]);
$nextFrame = thorm(['get', read($framesAtom), $tickIndex]);

$rowTpl = el('div', [style(['display' => 'flex'])], [
    repeat(item('cells'), concat(read($frameIndex), '-', item('id')),
        el('div', [
            style([
                'width' => '12px',
                'height' => '12px',
                'border' => '1px solid #222',
                'background' => cond(eq(item('alive'), val(1)), '#00d27a', '#111')
            ])
        ], [])
    ),
]);

$app = fragment([
    el('div', [attrs(['class' => 'container my-5 p-3'])], [
        client(
            div([ cls('glass p-3 rounded-2') ], [
                el('h1', [], [ text('Conway Game of Life') ]),
                p([cls('text-muted')], [
                    text('A precomputed demo using thorm() for tick math and simple controls.')
                ]),
                el('div', [cls('d-flex gap-2 align-items-center my-3')], [
                    el('button', [
                        cls('btn btn-primary'),
                        on('click', set($running, cond(read($running), val(false), val(true))))
                    ], [ text(cond(read($running), 'Pause', 'Run')) ]),
                    el('button', [
                        cls('btn btn-outline-dark'),
                        on('click', set($speed, thorm(['max', ['sub', read($speed), 1], 1])))
                    ], [ text('Speed -') ]),
                    el('button', [
                        cls('btn btn-outline-dark'),
                        on('click', set($speed, thorm(['min', ['add', read($speed), 1], 10])))
                    ], [ text('Speed +') ]),
                    el('span', [cls('text-dark ms-2')], [
                        text(concat('Speed: ', read($speed), ' | Frame: ', read($frameIndex)))
                    ]),
                ]),
                el('div', [style(['display' => 'inline-block', 'padding' => '8px', 'background' => '#0d0d0d'])], [
                    repeat(read($currentFrame), concat(read($frameIndex), '-', item('id')), $rowTpl)
                ]),
            ])
        ),
        $precomputeCode,
        $code,
    ]),
    every(200, [
        set($frameIndex, $tickIndex, true),
        set($currentFrame, $nextFrame, true)
    ]),
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
        'title'         => 'Conway Game of Life',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
