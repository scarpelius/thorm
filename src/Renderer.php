<?php
declare(strict_types=1);

namespace PhpJs;

use PhpJs\IR\Atom;

use PhpJs\IR\Action\Listener;

use PhpJs\IR\Expr\Expr;
use PhpJs\IR\Expr\ExprConcat;
use PhpJs\IR\Expr\ExprItem;
use PhpJs\IR\Expr\ExprOp;
use PhpJs\IR\Expr\ExprRead;
use PhpJs\IR\Expr\ExprStringify;
use PhpJs\IR\Expr\ExprVal;

use PhpJs\IR\Node\Node;
use PhpJs\IR\Node\EffectNode;
use PhpJs\IR\Node\ElNode;
use PhpJs\IR\Node\FragmentNode;
use PhpJs\IR\Node\ListNode;
use PhpJs\IR\Node\ShowNode;
use PhpJs\IR\Node\TextNode;

final class Renderer {
    /** @var Atom[] */
    private array $atoms = [];

    private function collectAtoms(mixed $x): void {
        // Direct atoms
        if ($x instanceof Atom) {
            $this->atoms[$x->id] = $x;
            return;
        }

        // Expressions
        if ($x instanceof ExprVal) return;
        if ($x instanceof ExprRead) { $this->collectAtoms($x->a); return; }
        if ($x instanceof ExprOp)   { 
            $this->collectAtoms($x->a);
            $this->collectAtoms($x->b);
            $this->collectAtoms($x->c);
            return; 
        }
        if ($x instanceof ExprConcat) {
            foreach ($x->parts as $p) $this->collectAtoms($p);
            return;
        }
        if($x instanceof ExprStringify) {
            $this->collectAtoms($x->value);
        }

        // Event actions
        if ($x instanceof Listener) {
            $this->collectAtoms($x->atom);
            // 'set' payload might be an Expr
            if (property_exists($x, 'payload') && $x->payload instanceof Expr) {
                $this->collectAtoms($x->payload);
            }
            return;
        }

        // Nodes
        if ($x instanceof TextNode) {
            $this->collectAtoms($x->value);
            return;
        }
        if ($x instanceof ShowNode) {
            $this->collectAtoms($x->cond);
            $this->collectAtoms($x->child);
            return;
        }
        if ($x instanceof ElNode) {
            // children
            foreach ($x->children as $c) $this->collectAtoms($c);
            // props come in as helper arrays: ['attrs', ...], ['cls', ...], ['style', ...], ['on', $event, Listener]
            foreach ($x->props as $p) {
                //print_r($p);
                if (!is_array($p) || !isset($p[0])) {
                    $this->collectAtoms($p->triggers);
                    if ($p instanceof EffectNode) {
                        foreach($p->actions as $action)
                            $this->collectAtoms($action);
                    }
                } else {
                    if ($p[0] === 'attrs') {
                        foreach (($p[1] ?? []) as $v) if ($v instanceof Expr) $this->collectAtoms($v);
                    } elseif ($p[0] === 'cls') {
                        if (($p[1] ?? null) instanceof Expr) $this->collectAtoms($p[1]);
                    } elseif ($p[0] === 'on') {
                        if (isset($p[2]) && $p[2] instanceof Listener) $this->collectAtoms($p[2]);
                    } 
                }
            }
            return;
        }
        if($x instanceof FragmentNode){
            // children
            foreach ($x->children as $c) $this->collectAtoms($c);
        }
        if ($x instanceof EffectNode) {
            foreach ($x->actions as $a) $this->collectAtoms($a);
            foreach ($x->triggers as $t) {
                if (is_array($t) && ($t['type'] ?? null) === 'watch') {
                    $expr = $t['expr'] ?? null;
                    if ($expr instanceof Expr) $this->collectAtoms($expr);
                }
            }
            return;
        }

        // ListNode: walk items expr, key expr, and the template subtree
        if ($x instanceof ListNode) {
            $this->collectAtoms($x->items);
            $this->collectAtoms($x->key);
            $this->collectAtoms($x->template);
            return;
        }

        // ExprItem: nothing to collect (no atoms inside by definition)
        if ($x instanceof ExprItem) return;

        // Fallback: arrays (shouldn’t be needed if we walk objects)
        if (is_array($x)) {
            foreach ($x as $v) $this->collectAtoms($v);
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

    /** Render a full HTML page with runtime and bootstrap script. */
    public function renderPage(Node $root, array $opts = []): Array {
        $ir = $this->toIR($root);
        $scope['title'] = htmlspecialchars($opts['title'] ?? 'PhpJs App', ENT_QUOTES);
        $scope['containerId'] = htmlspecialchars($opts['containerId'] ?? 'app', ENT_QUOTES);
        $scope['runtimeSrc'] = $opts['runtimeSrc'] ?? '/assets/phpjs-runtime.js';
        $scope['iruri_dir'] = $opts['iruri_dir']??'';

        // ensure the unicity of the iruri
        $callerId = md5(debug_backtrace()[0]['file']);
        $scope['iruri'] = $callerId . '.ir.json';
        $scope['irJson'] = json_encode($ir, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $scope['runtimeJs'] = $this->runtimeJs();
        
        $tpl = file_get_contents($opts['template']);
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
