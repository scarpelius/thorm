<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use Thorm\IR\Action\Listener;
use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;
use Thorm\IR\Renderable;

/**
 * IR node for a standard HTML element.
 *
 * Stores element tag, normalized props helpers, child nodes, and optional
 * render target metadata.
 *
 * @group IR/Node
 * @example
 * $node = new ElNode(
 *     'button',
 *     [['attrs', ['type' => 'button']], ['cls', 'btn btn-primary']],
 *     [new TextNode('Save')]
 * );
 */
final class ElNode extends Node implements \JsonSerializable, AtomCollectable, Renderable {
    /**
     * Build an element IR node.
     *
     * @param string $tag Element tag name.
     * @param array<string, mixed> $props Props helper payload.
     * @param array<int, Node> $children Child IR nodes.
     * @param array<string, string>|null $render Optional render metadata.
     */
    public function __construct(
        public string $tag,
        public array $props = [],
        public array $children = [],
        public ?array $render = null
    ) {}

    /**
     * Collect atom-related dependencies referenced by this node.
     *
     * @param callable $collect Collector callback that receives dependency nodes.
     * @return void
     */
    public function collectAtoms(callable $collect): void
    {
        // children
        foreach ($this->children as $c) $collect($c);
        // props come in as helper arrays: ['attrs', ...], ['cls', ...], ['style', ...], ['on', $event, Listener]
        foreach ($this->props as $p) {
            //print_r($p);
            if (!is_array($p) || !isset($p[0])) {
                $collect($p->triggers);
                if ($p instanceof EffectNode) {
                    foreach($p->actions as $action)
                        $collect($action);
                }
            } else {
                if ($p[0] === 'attrs') {
                    foreach (($p[1] ?? []) as $v) if ($v instanceof Expr) $collect($v);
                } elseif ($p[0] === 'cls') {
                    if (($p[1] ?? null) instanceof Expr) $collect($p[1]);
                } elseif ($p[0] === 'on') {
                    if (isset($p[2]) && $p[2] instanceof Listener) $collect($p[2]);
                }
            }
        }
    }

    public function render(callable $renderer): string
    {
        $tag = (string)($this->node['tag'] ?? 'div');
        $props = $this->node['props'] ?? [];
        $attrs = $this->renderProps($props, $this->ctx);
        $isClientTarget = (($this->node['render']['target'] ?? null) === 'client');
        if ($isClientTarget) {
            $lower = strtolower($tag);
            if (isset(self::VOID_TAGS[$lower])) {
                return '<' . $tag . $attrs . '>';
            }
            return '<' . $tag . $attrs . '></' . $tag . '>';
        }
        $children = $this->renderNodes($this->node['children'] ?? [], $this->ctx);
        $lower = strtolower($tag);
        if (isset(self::VOID_TAGS[$lower])) {
            return '<' . $tag . $attrs . '>';
        }
        return '<' . $tag . $attrs . '>' . $children . '</' . $tag . '>';
    }

    /**
     * Encode this node as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed {
        $props = ['attrs'=>[], 'cls'=>null, 'style'=>[], 'on'=>[]];
        foreach ($this->props as $k => $v) {
            if ($k === 0 && is_array($v) && isset($v[0]) && $v[0] === 'attrs') {
                $props['attrs'] = $v[1];
            } elseif ($k === 0 && is_array($v) && isset($v[0]) && $v[0] === 'cls') {
                $props['cls'] = $v[1];
            } elseif ($k === 0 && is_array($v) && isset($v[0]) && $v[0] === 'style') {
                $props['style'] = $v[1];
            }
        }
        // Allow props as a list of helper arrays, e.g., [attrs([...]), cls(...), on(...), on(...)].
        foreach ($this->props as $item) {
            if (is_array($item) && isset($item[0])) {
                if ($item[0] === 'attrs')
                    $props['attrs'] = array_replace($props['attrs'] ?? [], (array)$item[1]);
                elseif ($item[0] === 'cls')
                    $props['cls'] = $item[1];
                elseif ($item[0] === 'style'){
                    if($item[1] instanceof Expr) {
                        $props['style'] = $item[1];
                    } else {
                        $props['style'] = array_replace($props['style'] ?? [], (array)$item[1]);
                    }
                }
                elseif ($item[0] === 'on')
                    $props['on'][] = [$item[1], $item[2]];
            }
        }
        $out = [
            'k' => 'el',
            'tag' => $this->tag,
            'props' => $this->encodeProps($props),
            'children' => $this->children
        ];
        if ($this->render !== null) {
            $out['render'] = $this->render;
        }
        return $out;
    }

    /**
     * Normalize props into the runtime wire format.
     *
     * @param array<string, mixed> $props
     * @return array<string, mixed>
     */
    private function encodeProps(array $props): array {
        $enc = ['attrs'=>[], 'style'=>[], 'on'=>[]];
        foreach ($props['attrs'] ?? [] as $name => $val) {
            $enc['attrs'][$name] = $val instanceof Expr ? $val : ['k'=>'val','v'=>(string)$val];
        }
        if (($props['cls'] ?? null) !== null) {
            $enc['cls'] = $props['cls'] instanceof Expr ? $props['cls'] : ['k'=>'val','v'=>(string)$props['cls']];
        }
        if($props['style'] instanceof Expr) {
            $enc['style'] = $props['style'];
        } else {
            foreach ($props['style'] ?? [] as $name => $val) {
                $enc['style'][$name] = $val;
            }
        }
        foreach ($props['on'] ?? [] as $pair) {
            [$event, $action] = $pair;
            $enc['on'][] = ['event'=>$event, 'action'=>$action];
        }
        return $enc;
    }
}
