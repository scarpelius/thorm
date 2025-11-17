<?php
declare(strict_types=1);

namespace PhpJs\IR\Expr;

/**
 * ExprQuery
 *
 * Expression node that reads a value from the current URL query string.
 * Example: for "/search?q=chair", query('q') → "chair".
 *
 * Evaluated via evaluate(..., ctx) → (ctx.route.query[name] ?? null).
 * Not reactive by itself; route changes remount the subtree so expressions re-evaluate.
 */
final class ExprQuery extends Expr implements \JsonSerializable {
    public function __construct(public string $name) {}
    
    public function jsonSerialize(): mixed {
        return ['k' => 'query', 'name' => $this->name];
    }
}
