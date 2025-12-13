<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use Thorm\IR\Expr\Expr;

/**
 * @param array<string, mixed> $props
 * @param array<int, Node> $children
 */
final class ElNode extends Node implements \JsonSerializable {
    /** @param array<int, Node> $children */
    public function __construct(
        public string $tag,
        public array $props = [],
        public array $children = []
    ) {}

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
        // Allow props as a list of helper arrays, e.g., [attrs([...]), cls(...), on(...), on(...)]
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
        return [
            'k' => 'el',
            'tag' => $this->tag,
            'props' => $this->encodeProps($props),
            'children' => $this->children
        ];
    }

    /** @param array<string, mixed> $props */
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
