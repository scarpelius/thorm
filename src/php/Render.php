<?php
declare(strict_types=1);

namespace Thorm;

use Thorm\IR\Atom;
use Thorm\IR\AtomCollectable;
use Thorm\IR\Node\Node;
use Thorm\IR\Renderable;

final class Render
{
    /** @var array<int, mixed> */
    private array $atoms = [];

    public array $ctx = [];

    public function __construct()
    {
    }

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

    /** @return array{html:string,state:array{atoms:array<int,mixed>},ir:array} */
    public function render(Node $root, array $opts = []): array
    {
        $ir = $this->toIR($root);
        
        $this->atoms = $this->buildAtoms($ir['atoms'] ?? [], $opts['atoms'] ?? null);
        $this->ctx = $opts['ctx'] ?? [];
        $html = $this->renderNode($ir['root'], $this->ctx);
        return [
            'html' => $html, 
            'state' => ['atoms' => $this->atoms], 
            'ir' => $ir
        ];
    }

    /** @param array<int, mixed> $irAtoms */
    private function buildAtoms(array $irAtoms, mixed $override): array
    {
        $out = [];
        foreach ($irAtoms as $a) {
            if (is_array($a) && array_key_exists('id', $a)) {
                $out[(int)$a['id']] = $a['initial'] ?? null;
            }
        }

        if (is_array($override)) {
            $isList = $override === [] || array_keys($override) === range(0, count($override) - 1);
            if ($isList) {
                foreach ($override as $item) {
                    if (is_array($item) && array_key_exists('id', $item)) {
                        $out[(int)$item['id']] = $item['value'] ?? ($item['initial'] ?? null);
                    }
                }
            } else {
                foreach ($override as $id => $value) {
                    $out[(int)$id] = $value;
                }
            }
        }

        return $out;
    }

    private function renderNode(array|string $node, array $ctx = []): string
    {
        if (is_string($node)) {
            return $node;
        }

        $kind = (string)($node['k'] ?? '');
        if ($kind === '') {
            return '';
        }

        $nodeClass = $kind === 'repeat'
            ? 'ListNode'
            : ucfirst(strtolower($kind)) . 'Node';
        $fqcn = "\\Thorm\\IR\\Node\\{$nodeClass}";

        if (class_exists($fqcn)) {
            $ref = new \ReflectionClass($fqcn);
            $nodeObj = $ref->newInstanceWithoutConstructor();

            if ($nodeObj instanceof Node && $nodeObj instanceof Renderable) {
                $nodeObj->setRenderContext(
                    $node,
                    $ctx,
                    $this->atoms,
                    fn(array|string $child, ?array $childCtx = null): string
                        => $this->renderNode($child, $childCtx ?? $ctx)
                );
                return $nodeObj->render(
                    fn(array|string $child, ?array $childCtx = null): string
                        => $this->renderNode($child, $childCtx ?? $ctx)
                );
            }
        }

        return '';
    }
}
