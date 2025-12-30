<?php
declare(strict_types=1);

namespace Thorm\IR\Expr;

/**
 * ExprParam
 *
 * Expression node that reads a value from the current route's path params
 * (e.g., for "/auction/:id", param('id') yields the matched "id").
 *
 * Evaluated via evaluate(..., ctx) → (ctx.route.params[name] ?? null).
 * Not reactive by itself; route changes remount the subtree so expressions re-evaluate.
 */
final class ExprParam extends Expr implements \JsonSerializable {
    public function __construct(public string $name) {}
    
    public function jsonSerialize(): mixed {
        return ['k' => 'param', 'name' => $this->name];
    }
}
