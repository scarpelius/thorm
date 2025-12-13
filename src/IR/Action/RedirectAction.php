<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use Thorm\IR\Expr\Expr;

final class RedirectAction implements Action
{
    public function __construct(
        public readonly Expr|string $to,
        public readonly bool $replace = false
    ) {}

    public function kind(): string { return 'redirect'; }

    public function jsonSerialize(): array
    {
        $to = $this->to instanceof \JsonSerializable ? $this->to->jsonSerialize() : $this->to;
        $out = ['k' => $this->kind(), 'to' => $to];
        if($this->replace) {
            $out['replace'] = $this->replace;
        }
        return $out;
    }
}
