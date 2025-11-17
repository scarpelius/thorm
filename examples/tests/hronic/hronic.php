<?php
declare(strict_types=1);
require __DIR__ . '/../../../vendor/autoload.php';
use function PhpJs\{fragment};
use PhpJs\Renderer;
use Hronic\Components\Header;
use Hronic\Components\Content;
use Hronic\Components\Footer;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }

$app = fragment([
    Header::get(),
    Content::get(),
    Footer::get(),
]);

$test = strtolower(pathinfo(__FILE__, PATHINFO_FILENAME));
$path = __DIR__.'/../../../public/tests/'.$test.'/';
if(!is_dir($path)) { mkdir($path); }

$renderer = new Renderer();
$res = $renderer->renderPage($app, [
    'title'         => 'Hronic - Aventuri SF&F',
    'containerId'   => 'app',
    'template'      => __DIR__.'/../../../assets/index-hronic.tpl.html',
]);
// save the bootstrap data
$html = file_put_contents($path . $res['iruri'], $res['irJson']);
// save the page
$json_data = file_put_contents(
    $path . 'index.html', 
    $res['tpl']
);

if($html !== false ) { echo green("Wrote html file\n"); } else { echo red("Bad luck, could not write html file.\n"); }
if($json_data !== false ) { echo green("Wrote JSON data file\n"); } else { echo red("Bad luck, could not write JSON file.\n"); }
echo "\n";

