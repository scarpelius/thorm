<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use Thorm\IR\Expr\Expr;

/**
 * IR node for trusted/raw HTML content.
 *
 * Accepts either a static string or an expression resolved at runtime.
 *
 * @group IR/Node
 * @example
 * $node = new HtmlNode('<strong>Hello</strong>');
 */
final class HtmlNode extends Node {
    /**
     * Build an HTML node.
     *
     * @param Expr|string $value Raw HTML value or expression.
     */
    public function __construct(public Expr|string $value) {}

    /**
     * Encode this HTML node as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed {
        return ['k' => 'html', 'value' => $this->value instanceof Expr ? $this->value : ['k'=>'val','v'=>(string)$this->value]];
    }
}
