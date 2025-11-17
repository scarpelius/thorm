<?php
declare(strict_types=1);

namespace PhpJs\IR\Action;

/**
 * Base contract for Action IR nodes (inc, set, add, http, navigate, ...).
 * Implementations MUST serialize to a pure array and expose a stable kind().
 */
interface Action extends \JsonSerializable
{
    /** Discriminator (e.g., "inc", "set", "add", "http", "navigate"). */
    public function kind(): string;

    /**
     * Return a normalized array suitable for JSON encoding.
     * (Signature stays mixed to comply with JsonSerializable.)
     *
     * @return array
     */
    public function jsonSerialize(): mixed;
}
