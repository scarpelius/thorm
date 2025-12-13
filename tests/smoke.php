<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use function Thorm\state;
use function Thorm\read;
use function Thorm\add;
use Thorm\Renderer;
use function Thorm\val;

$atom = state(2);
$expr = add(read($atom), val(3));
$renderer = new Renderer();
$ir = $renderer->toIR(\Thorm\text($expr));
echo json_encode($ir, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
