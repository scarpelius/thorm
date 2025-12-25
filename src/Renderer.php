<?php
declare(strict_types=1);

namespace Thorm;

use Thorm\IR\Atom;
use Thorm\IR\AtomCollectable;
use Thorm\IR\Node\Node;

final class Renderer {
    /** @var Atom[] */
    private array $atoms = [];

    private function collectAtoms(mixed $x): void {
        // Direct atoms
        if ($x instanceof Atom) {
            $this->atoms[$x->id] = $x;
            //return;
        } elseif($x instanceof AtomCollectable) {
            $x->collectAtoms(fn($c) => $this->collectAtoms($c));
        } elseif(is_array($x)) {
            foreach ($x as $v) $this->collectAtoms($v);
        } else {
            // no op
        }
    }

    /** @return array{atoms: array<array{id:int,initial:mixed}>, root: mixed} */
    public function toIR(Node $root): array {
        $this->atoms = [];
        $this->collectAtoms($root);
        $rootJson = $root->jsonSerialize();
        // Serialize atoms
        $atoms = array_map(
            fn(Atom $a) => $a->jsonSerialize(), 
            array_values($this->atoms)
        );
        return ['atoms' => $atoms, 'root' => $rootJson];
    }

    /** 
     * Render a full HTML page with runtime and bootstrap script. 
     * @var Node $root, root node 
     * @var array $opts
     *      $opts['title'], page title
     *      $opts['containerId'], container id
     *      $opts['runtimeSrc'], runtime source
     *      $opts['iruri_dir'], IR directory path
     *      $opts['template'], path to html template
     *      $opts['template_as_string'], template given as string
    */
    public function renderPage(Node $root, array $opts = []): Array {
        $ir = $this->toIR($root);
        $scope['title'] = htmlspecialchars($opts['title'] ?? 'Thorm App', ENT_QUOTES);
        $scope['containerId'] = htmlspecialchars($opts['containerId'] ?? 'app', ENT_QUOTES);
        //$scope['runtimeSrc'] = $opts['runtimeSrc'] ?? '/assets/Thorm-runtime.js';
        $scope['iruri_dir'] = $opts['iruri_dir']??'';

        // ensure the unicity of the iruri
        $callerId = md5(debug_backtrace()[0]['file']);
        $scope['iruri'] = $callerId . '.ir.json';
        $scope['irJson'] = json_encode($ir, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $scope['runtimeJs'] = $this->runtimeJs();
        
        $tpl = array_key_exists('template_as_string', $opts) 
            ? $opts['template'] 
            : file_get_contents($opts['template']);
        $tpl = preg_replace_callback('/{\$(\w+)}/', function($matches) use($scope)  {
            return $scope[$matches[1]] ?? ''; // look up variable by name
        }, $tpl);
        
        return [
            'iruri'     => $scope['iruri'],
            'irJson'    => $scope['irJson'],
            'tpl'       => $tpl,
        ];
    }

    /** Inline the tiny runtime for the MVP. */
    private function runtimeJs(): string {
        // Load the file from assets to keep a single source of truth
        $path = __DIR__ . '/../assets/runtime.js';
        if (is_file($path)) {
            return file_get_contents($path) ?: '';
        }
        return 'console.error("runtime not found")';
    }
}
