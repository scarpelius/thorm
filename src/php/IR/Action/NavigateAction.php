<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use Thorm\IR\Expr\Expr;

final class NavigateAction implements Action
{
    public function __construct(
        public readonly Expr|string $to
    ) {}

    public function kind(): string { return 'navigate'; }

    public function jsonSerialize(): array
    {
        $to = $this->to instanceof \JsonSerializable ? $this->to->jsonSerialize() : $this->to;
        return ['k' => $this->kind(), 'to' => $to];
    }
}
