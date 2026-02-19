<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use Thorm\IR\Expr\Expr;

/**
 * Action that performs client-side navigation.
 *
 * @group IR/Action
 * @example
 * $action = new NavigateAction('/docs');
 */
final class NavigateAction implements Action
{
    /**
     * Build a navigate action.
     *
     * @param Expr|string $to Target URL.
     */
    public function __construct(
        public readonly Expr|string $to
    ) {}

    /**
     * Return action discriminator.
     *
     * @return string
     */
    public function kind(): string { return 'navigate'; }

    /**
     * Encode this action as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $to = $this->to instanceof \JsonSerializable ? $this->to->jsonSerialize() : $this->to;
        return ['k' => $this->kind(), 'to' => $to];
    }
}
