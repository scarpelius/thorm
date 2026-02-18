<?php
declare(strict_types=1);
namespace Thorm\IR\Effect;

/**
 * Effect target bound to `document`.
 *
 * @group IR/Effect
 * @example
 * $target = new DocumentTarget();
 */
final class DocumentTarget implements EffectTarget {
    /**
     * Encode this target as runtime IR payload.
     *
     * @return array<string, string>
     */
    public function jsonSerialize(): array { return ['type'=>'document']; }
}
