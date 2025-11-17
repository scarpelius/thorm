<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use function PhpJs\state;
use function PhpJs\read;
use function PhpJs\add;
use PhpJs\Renderer;
use function PhpJs\val;

$atom = state(2);
$expr = add(read($atom), val(3));
$renderer = new Renderer();
$ir = $renderer->toIR(\PhpJs\text($expr));
echo json_encode($ir, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
