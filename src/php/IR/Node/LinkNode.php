<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use Thorm\IR\Expr\Expr;

/**
 * IR node for declarative navigation links.
 *
 * Encodes destination expression, link props, and child nodes for runtime
 * navigation handling.
 *
 * @group IR/Node
 * @example
 * $node = new LinkNode(
 *     Expr::val('/docs'),
 *     [['cls', 'link-light']],
 *     [new TextNode('Docs')]
 * );
 */
final class LinkNode extends Node implements \JsonSerializable {
    /**
     * Build a link IR node.
     *
     * @param Expr $to Destination expression.
     * @param array<int, mixed> $props Link props helper payload.
     * @param array<int, Node> $children Child IR nodes.
     */
    public function __construct(
        public Expr $to,
        public array $props = [],
        public array $children = [],
    ) {}

    /**
     * Encode this link node as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed {
        // Normalize props to match ElNode semantics: 'cls' must be an Expr.
        $normProps = [];
        foreach ($this->props as $p) {
            if (!is_array($p) || !isset($p[0])) continue;

            if ($p[0] === 'cls') {
                $v = $p[1] ?? '';
                $expr = $v instanceof Expr ? $v : Expr::val((string)$v);
                $normProps[] = ['cls', $expr];
                continue;
            }

            if ($p[0] === 'attrs') {
                // Keep attr values as-is: scalars are static, Expr are reactive (runtime handles both)
                $map = [];
                foreach (($p[1] ?? []) as $k => $v) { $map[$k] = $v; }
                $normProps[] = ['attrs', $map];
                continue;
            }

            // 'on' and any other prop kinds pass through (already serialized)
            $normProps[] = $p;
        }

        return [
            'k'        => 'link',
            'to'       => $this->to,
            'props'    => $normProps,
            'children' => $this->children,
        ];
    }
}
