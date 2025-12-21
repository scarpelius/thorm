<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use Thorm\IR\Expr\Expr;

final class PushAction implements Action
{
    public function __construct(
        public readonly int $atomId,
        public readonly Expr|int|float|string|bool|array|null $value
    ) {}

    public function kind(): string { return 'push'; }

    public function jsonSerialize(): array
    {
        $v = $this->value;
        if ($v instanceof \JsonSerializable) $v = $v->jsonSerialize();
        return ['k' => 'push', 'atom' => $this->atomId, 'value' => $v];
    }
}
