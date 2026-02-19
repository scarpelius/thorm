<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use Thorm\IR\Expr\Expr;

/**
 * Action that redirects navigation to another URL.
 *
 * @group IR/Action
 * @example
 * $action = new RedirectAction('/login', true);
 */
final class RedirectAction implements Action
{
    /**
     * Build a redirect action.
     *
     * @param Expr|string $to Redirect target URL.
     * @param bool $replace Whether to replace history entry.
     */
    public function __construct(
        public readonly Expr|string $to,
        public readonly bool $replace = false
    ) {}

    /**
     * Return action discriminator.
     *
     * @return string
     */
    public function kind(): string { return 'redirect'; }

    /**
     * Encode this action as runtime IR payload.
     *
     * @return array<string, mixed>
     */
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
