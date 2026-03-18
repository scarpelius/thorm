<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use function Thorm\{el, text, concat, read, on, cls, state, inc, html, client};
use Thorm\BuildExample;
use Thorm\Render;

function green($s){ return "\033[32m{$s}\033[0m"; }
function red($s){ return "\033[31m{$s}\033[0m"; }
function randomLoremHtml(int $minWords = 50, int $maxWords = 120): string
{
    $words = explode(' ', 'lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua ut enim ad minim veniam quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur excepteur sint occaecat cupidatat non proident sunt in culpa qui officia deserunt mollit anim id est laborum');
    $count = random_int($minWords, $maxWords);
    $tags = ['p', 'span', 'strong', 'em', 'i', 'b', 'mark', 'small', 'u'];
    $html = '';

    $i = 0;
    while ($i < $count) {
        $tag = $tags[array_rand($tags)];
        $chunkSize = random_int(3, 12);
        $chunkWords = [];
        for ($j = 0; $j < $chunkSize && $i < $count; $j++, $i++) {
            $chunkWords[] = $words[array_rand($words)];
        }
        $html .= "<{$tag}>" . implode(' ', $chunkWords) . "</{$tag}>";
        if (random_int(0, 3) === 0) {
            $html .= "<br/>";
        }
    }
    return $html;
}

$html = randomLoremHtml();

$app = el('div', [ cls('container') ], [
    el('h1', [], [ text('HTML content')]),
    el('div', [], [ html($html) ]),

]);


$app = client($app);

$renderer = new Render();
$res = $renderer->render($app);

$build = BuildExample::build([
    'name'          => strtolower(pathinfo(__FILE__, PATHINFO_FILENAME)),
    'path'          => __DIR__.'/../../public/tests/',
    'renderer'      => $res,
    'template'      => __DIR__.'/../../assets/index-test.tpl.html',
    'opts'          => [
        'title'         => 'HTML Content',
        'containerId'   => 'app',
    ],
]);

if($build !== false ) {
    echo green("File wrote to disk.\n");
} else {
    echo red("Could not write files to disk.\n");
}
echo "\n";
