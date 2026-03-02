<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use Thorm\IR\Expr\Expr;
use Thorm\IR\Renderable;

/**
 * IR node for trusted/raw HTML content.
 *
 * Accepts either a static string or an expression resolved at runtime.
 *
 * @group IR/Node
 * @example
 * $node = new HtmlNode('<strong>Hello</strong>');
 */
final class HtmlNode extends Node implements Renderable {
    /**
     * Build an HTML node.
     *
     * @param Expr|string $value Raw HTML value or expression.
     */
    public function __construct(public Expr|string $value) {}

    public function render(callable $renderer): string
    {
        $v = $this->evalExpr($this->node['value'] ?? null, $this->ctx);
        $markup = $v == null ? '' : (string)$v;
        return $this->comment('html:start') . $markup . $this->comment('html:end');
    }

    /**
     * Encode this HTML node as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): mixed {
        return ['k' => 'html', 'value' => $this->value instanceof Expr ? $this->value : ['k'=>'val','v'=>(string)$this->value]];
    }
}
