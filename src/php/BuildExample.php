<?php 
declare(strict_types=1);
namespace Thorm;

class BuildExample
{
    public static function build(array $data): bool
    {
        $path = $data['path'].$data['name'].'/';
        if(!is_dir($path)) { mkdir($path); }

        $opts = is_array($data['opts'] ?? null) ? $data['opts'] : [];

        $scope['title'] = htmlspecialchars($opts['title'] ?? 'Thorm App', ENT_QUOTES);
        $scope['containerId'] = htmlspecialchars($opts['containerId'] ?? 'app', ENT_QUOTES);

        // ensure the unicity of the iruri
        $callerId = md5(debug_backtrace()[0]['file']);
        $scope['iruri'] = $callerId . '.ir.json';
        $scope['irJson'] = json_encode(
            $data['renderer']['ir'], 
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
        
        $tpl = file_get_contents($data['template']);
        $tpl = preg_replace_callback('/{\$(\w+)}/', function($matches) use($scope)  {
            return $scope[$matches[1]] ?? ''; // look up variable by name
        }, $tpl);

        // save the bootstrap data
        $ir = file_put_contents($path . $scope['iruri'], $scope['irJson']);
        // save the page
        $html = file_put_contents(
            $path . 'index.html', 
            $tpl
        );

        return $ir && $html;
    }
}
