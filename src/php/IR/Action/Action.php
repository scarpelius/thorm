<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

/**
 * Base contract for Action IR nodes (inc, set, add, http, navigate, ...).
 * Implementations MUST serialize to a pure array and expose a stable kind().
 *
 * @group IR/Action
 * @example
 * $action = new IncAction(1, 1);
 */
interface Action extends \JsonSerializable
{
    /**
     * Return action discriminator.
     *
     * @return string
     */
    public function kind(): string;

    /**
     * Return a normalized array suitable for JSON encoding.
     * (Signature stays mixed to comply with JsonSerializable.)
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed;
}
