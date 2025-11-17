<?php
declare(strict_types=1);

namespace PhpJs\IR\Node;

use PhpJs\IR\Expr\Expr;

final class LinkNode extends Node implements \JsonSerializable {
    /** @param array<int, mixed> $props  same shape as ElNode props: ['attrs', map], ['cls', expr], ... */
    /** @param array<int, Node> $children */
    public function __construct(
        public Expr $to,
        public array $props = [],
        public array $children = [],
    ) {}

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
